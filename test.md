---
layout: post
title: "API Designer is gone. Assembly isn’t."
date: 2026-07-18 10:30:00 +0400
permalink: /test/
description: "IBM API Connect v12 replaces API Designer with API Studio — but assembly is not gone. It becomes a named, versioned, reusable policy sequence. A practical Designer-to-Studio map for v10 veterans."
categories: [IBM API Connect]
tags: [api-connect, ibm, api-studio, api-designer, api-management, integration, assembly, policy-sequence]
---

> **Preview draft** — hosted at `/test/` for review. Not listed on Home or Blog yet.

If you lived in API Connect v10, the first look at v12 can feel like someone stole your Assembly canvas. Relax. **API Designer is deprecated and replaced by IBM API Studio** — but the assembly model did not disappear. Studio turns that flow into a named, versioned, reusable **policy sequence** so API Connect can scale like modern software delivery: Git, reuse, multi-gateway, and AI tooling.

<!--more-->

This post is for people who already know Designer, `x-ibm-configuration`, and the Assembly execute list — and just need a clean mental remap.

## The short answer

| Question | Answer |
|----------|--------|
| Is API Designer gone in v12? | **Deprecated / replaced by IBM API Studio** |
| Is assembly gone? | **No.** It became a **policy sequence** |
| Do I still have invoke, map, switch, security? | **Yes** — same policy concepts |
| What actually changed? | Assets are **split and reusable**, not one mega-YAML per API |

[Screenshot: Studio project tree with api-spec, kind: api, and policy-sequence files]

## Old mental model (Designer)

In Designer, one OpenAPI/Swagger YAML usually held **everything**:

- The contract (paths, schemas, security schemes)
- IBM extensions under `x-ibm-configuration`
- The assembly under `assembly.execute`
- Often the Product story nearby, with Plans nested in one Product YAML

The **visual Assembly canvas** was the main authoring experience. You dragged policies, wired invoke → map → switch, then tested with the **Test** tab, **Try It**, **LTE**, or Explorer.

That model was fast for a single API. It was painful when you needed the same flow on twenty APIs, clean Git diffs, or multi-team ownership of the same governance rules.

## New mental model (Studio)

Studio is **project-centric and code-first**. You work in a workspace (local folder, Git repo, or native storage) and split what used to be one blob into clear assets:

| Studio asset | What it is (Designer mental map) |
|--------------|----------------------------------|
| **api-spec** | Clean OpenAPI contract — paths, schemas, security |
| **kind: api** | API metadata + links to spec, gateways, policy sequences |
| **policy-sequence** | The old assembly `execute` list — now a reusable object |
| **Product / Plan** | Separate reusable files, not always nested in one Product YAML |
| **Tests / assertions / environments** | First-class project assets, not only a Test tab moment |

You still get **Form View** and **Code View**. Form View is the spiritual successor to the canvas for people who think in policies; Code View is where `$ref` reuse becomes obvious.

Policy editing is also **gateway-type-aware**. When you create a sequence, you pick a runtime such as:

- DataPower API Gateway
- DataPower Nano Gateway
- webMethods API Gateway

The editor surfaces policies that runtime supports. That matters: Nano and webMethods speak the Studio artifact model — do not assume a classic v10 DataPower API Gateway API drops onto them unchanged.

## The key insight: assembly became an attachable object

What used to be “the assembly **on this API**” is now a **policy sequence** you can attach at:

1. **Global** — project-wide or a selected set of APIs  
2. **API level** — all paths/methods on that API  
3. **Scope level** — a specific path/method  

Attachment looks like this in Code View:

```yaml
policy-seq:
  $ref: orders:orders-backend-flow:1.0.0
```

That one line is the whole paradigm shift: the flow is no longer trapped inside the API YAML. It is a versioned asset other APIs (and scopes) can reuse.

## Side-by-side: same Orders flow

Same job in both worlds: **invoke backend → map response**.

### A) Classic Designer-style (single file excerpt)

```yaml
# orders-1.0.0.yaml (contract + IBM config + assembly together)
openapi: 3.0.3
info:
  title: Orders API
  version: 1.0.0
paths:
  /orders/{orderId}:
    get:
      operationId: getOrder
      parameters:
        - name: orderId
          in: path
          required: true
          schema:
            type: string
      responses:
        "200":
          description: Order details

x-ibm-configuration:
  gateway: datapower-api-gateway
  assembly:
    execute:
      - invoke:
          title: invoke-orders-backend
          target-url: $(orders-backend-url)/orders/$(request.parameters.orderId)
          verb: GET
      - map:
          title: map-order-response
          inputs:
            backend:
              schema:
                type: object
              variable: message.body
          outputs:
            response:
              schema:
                type: object
              variable: message.body
          actions:
            - set: response.orderId
              from: backend.id
            - set: response.status
              from: backend.orderStatus
```

Everything lives together. Reuse means copy-paste (or hope your team remembers which API was the “golden” one).

### B) Studio split (same execute logic, reusable)

**1. Clean OpenAPI (`api-spec`)**

```yaml
# orders-api-spec.yaml
openapi: 3.0.3
info:
  title: Orders API
  version: 1.0.0
paths:
  /orders/{orderId}:
    get:
      operationId: getOrder
      parameters:
        - name: orderId
          in: path
          required: true
          schema:
            type: string
      responses:
        "200":
          description: Order details
```

**2. Policy sequence (your old assembly)**

```yaml
# orders-backend-flow.yaml
kind: PolicySequence
metadata:
  name: orders-backend-flow
  namespace: orders
  version: 1.0.0
spec:
  gateway: datapower-api-gateway
  execute:
    - invoke:
        title: invoke-orders-backend
        target-url: $(orders-backend-url)/orders/$(request.parameters.orderId)
        verb: GET
    - map:
        title: map-order-response
        inputs:
          backend:
            schema:
              type: object
            variable: message.body
        outputs:
          response:
            schema:
              type: object
            variable: message.body
        actions:
          - set: response.orderId
            from: backend.id
          - set: response.status
            from: backend.orderStatus
```

**3. API metadata attaches the sequence**

```yaml
# orders-api.yaml
kind: api
metadata:
  name: orders
  namespace: orders
  version: 1.0.0
spec:
  type: rest
  api-spec: orders-api-spec.yaml
  policy-seq:
    $ref: orders:orders-backend-flow:1.0.0
```

Same invoke. Same map. Different packaging — and now `orders-backend-flow:1.0.0` can hang off another API or a single GET scope without cloning YAML.

[Screenshot: Form View Policy Sequence section on an API outline]

## What stays the same

Do not throw away your v10 instincts:

- **Policy concepts** — invoke, map, switch, security, transforms, rate limits, and friends still matter
- **Product / Plan / Catalog** — you still package APIs, subscribe consumers, and publish into catalogs
- **REST lifecycle** — design → secure → productize → publish is still the job

Studio changes **where those ideas live as files**, not whether they exist.

## What IBM is trying to achieve

This is not a UI refresh for its own sake. The strategic bet looks like this:

1. **API-as-code / Git-friendly assets** — small, named YAML files diff and review like application code  
2. **Reuse and governance** — one policy sequence version shared across APIs instead of twenty pasted assemblies  
3. **One Studio model across gateway types** — DataPower API Gateway, Nano, and webMethods under one authoring surface  
4. **Foundation for AI / agentic tooling** — API Agent, MCP-style automation, and AI-assisted tests need structured assets, not one opaque mega-file  
5. **Better DevOps and multi-team ownership** — contract owners, policy owners, and product owners can work on separate files in one project

If you only remember one sentence: **Studio does not kill assembly — it turns assembly into a reusable software object.**

## Practical migration tips for Designer veterans

**Day-1 path that builds the new muscle memory:**

1. Create a project (local or Git)  
2. Import your OpenAPI  
3. Create a policy sequence with the familiar invoke → map flow  
4. Attach it at **API level**  
5. Try a **scope-level** attach on one path/method  
6. Run Tryout / publish the way you already know conceptually  

**A few landmines:**

- Do **not** assume old v10 APIs drop unchanged onto Nano or webMethods gateways — those runtimes expect Studio’s artifact format  
- If you loved the canvas, start in **Form View**; switch to **Code View** once `$ref` clicks  
- Prefer matching **toolkit / Studio version** to the Manager you connect to — version skew creates confusing “works on my laptop” moments  
- Treat policy sequences like shared libraries: bump versions on purpose; do not silently edit a sequence used by half the catalog  

## Closing

If you are still asking “where did Assembly go?”, reframe the question.

It did not leave. It stopped being a private flow trapped inside one API YAML and became a **named, versioned, reusable policy sequence** you attach where you need it — globally, on the API, or on a single operation.

Designer is deprecated in v12. Assembly’s ideas are not. Studio is how those ideas scale.
