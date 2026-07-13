---
layout: post
title: "Enforcing Rate limits using dynamic keys from request headers"
date: 2026-07-13 11:15:00 +0400
categories: [IBM API Connect]
tags: [api-connect, rate-limiting, assembly, headers, ibm]
---

Not every API call should cost the same against your quota. A lightweight `create` is different from a heavy `update` or a destructive `delete` — yet many teams apply a single flat rate limit at the gateway and wonder why burst traffic still overwhelms backends.

In IBM API Connect, you can define **named assembly rate limits** on an API plan and then **consume the right limit at runtime** based on a value from the request — in my case, the `opname` header. Here is the pattern I use.

## The Problem

A single endpoint handles multiple logical operations. Clients send an `opname` header (`create`, `update`, or `delete`) to indicate what they want to do. Each operation has a different cost profile:

| Operation | Relative cost | Why |
|-----------|---------------|-----|
| `create`  | Low           | Simple insert, minimal downstream calls |
| `update`  | Medium        | Validation + partial writes |
| `delete`  | High          | Cascading cleanup, audit trails |

A one-size-fits-all rate limit either blocks legitimate light traffic or lets expensive operations through too easily.

## Step 1 — Define Named Limits on the API Plan

In the API product plan, open **Assembly rate limits** and create one named limit per operation. The **Cost** field is the weight consumed per request; **Per** and **Unit** define the refill window.

![Assembly rate limits defined on the API plan]({{ '/assets/images/posts/api-connect-rate-limits/plan-assembly-rate-limits.png' | relative_url }})

In my setup:

- **create** — cost `5`, `1` per `minute`
- **update** — cost `10`, `1` per `minute`
- **delete** — cost `20`, `1` per `minute`

These names (`create`, `update`, `delete`) are the keys the assembly will reference later. Keeping names aligned with the header values avoids mapping logic in the gateway.

## Step 2 — Branch in the Assembly with a Switch

Add a **switch** policy at the top of the assembly (after authentication, before invoke). Each case matches `request.headers.opname` and routes to a dedicated **ratelimit** policy.

![Assembly switch routing to ratelimit policies by opname header]({{ '/assets/images/posts/api-connect-rate-limits/assembly-switch-ratelimit.png' | relative_url }})

The four branches:

1. `request.headers.opname = "create"` → ratelimit (create)
2. `request.headers.opname = "update"` → ratelimit (update)
3. `request.headers.opname = "delete"` → ratelimit (delete)
4. **Otherwise** → throw (reject unknown operations)

The **Otherwise** branch is important. Without it, requests with a missing or invalid `opname` would skip rate limiting entirely and still reach the backend.

## Step 3 — Configure Each Ratelimit Policy

Each ratelimit policy on a switch branch uses the same settings pattern; only the **Rate limit name** changes to match the plan definition.

![Ratelimit policy configured with plan-named source]({{ '/assets/images/posts/api-connect-rate-limits/ratelimit-policy-settings.png' | relative_url }})

Key fields:

| Field | Value | Purpose |
|-------|-------|---------|
| **Source** | `plan-named` | Pull limits from the API plan's named assembly rate limits |
| **Rate limit name** | `create` (or `update` / `delete`) | Selects which named bucket to use |
| **Rate limit operation** | `consume` | Deducts tokens when the request passes through |

Repeat this policy three times — one per switch case — changing only the rate limit name.

## How It Works End to End

1. Client calls the API with header `opname: create`.
2. The switch matches the `create` branch.
3. The ratelimit policy consumes `5` tokens from the `create` bucket defined on the plan.
4. If tokens remain, the request continues to invoke; if not, API Connect returns `429 Too Many Requests`.

A `delete` call on the same endpoint consumes from the `delete` bucket at cost `20`, so heavy operations are throttled more aggressively even though the URL is identical.

## Example Request

```bash
curl -X POST "https://api.example.com/v1/orders" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "opname: update" \
  -d '{"orderId": "ORD-1001", "status": "shipped"}'
```

With `opname: update`, the gateway hits the update branch and consumes from the `update` rate-limit bucket.

## Assembly Snippet (YAML)

For teams managing APIs as code, the same logic in OpenAPI extension / assembly YAML looks like this:

```yaml
assembly:
  execute:
    - switch:
        title: switch
        case:
          - condition: $(request.headers.opname) = 'create'
            execute:
              - ratelimit:
                  title: ratelimit-create
                  source: plan-named
                  rate-limits:
                    - name: create
                      operation: consume
          - condition: $(request.headers.opname) = 'update'
            execute:
              - ratelimit:
                  title: ratelimit-update
                  source: plan-named
                  rate-limits:
                    - name: update
                      operation: consume
          - condition: $(request.headers.opname) = 'delete'
            execute:
              - ratelimit:
                  title: ratelimit-delete
                  source: plan-named
                  rate-limits:
                    - name: delete
                      operation: consume
        otherwise:
          - throw:
              title: throw
              message: "Invalid or missing opname header"
              name: InvalidOperation
    - invoke:
        title: invoke
        target-url: $(target-url)
```

Adjust the `throw` message and fault name to match your API's error contract.

## Testing Tips

- **Verify each branch** — send requests with each `opname` value and confirm the correct limit is hit in analytics.
- **Exhaust one bucket** — flood `create` until you get `429`; confirm `update` still works on the same client credentials.
- **Test the Otherwise path** — omit `opname` or send `opname: patch` and confirm the throw policy fires before invoke.
- **Watch plan changes** — if you rename a limit on the plan, update every matching ratelimit policy in the assembly.

## Key Takeaways

- Named assembly rate limits on the plan let you define **multiple weighted buckets** in one subscription.
- A **switch on request headers** selects the right bucket without duplicating APIs or routes.
- **`plan-named` + `consume`** ties assembly policy to plan configuration — change limits in the product without redeploying assembly logic.
- Always add an **Otherwise** branch so malformed requests cannot bypass throttling.

This pattern works well for multiplexed endpoints, legacy systems that overload a single URL, and any API where operation cost varies but the routing surface stays flat.
