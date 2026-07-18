---
layout: post
title: "API Designer is gone. Assembly isn’t."
date: 2026-07-18 10:30:00 +0400
description: "IBM API Connect v12 replaces API Designer with API Studio — but assembly is not gone. It becomes a named, versioned, reusable policy sequence."
categories: [IBM API Connect]
tags: [api-connect, ibm, api-studio, api-designer, api-management, integration, assembly, policy-sequence]
---

If you opened API Connect v12 looking for Designer and the Assembly canvas, you are not alone. **API Designer is deprecated and replaced by IBM API Studio** — but assembly did not disappear. It became a named, versioned, reusable **policy sequence**.

<!--more-->

## What changed

| Designer (v10) | Studio (v12) |
|----------------|--------------|
| One YAML held contract + **x-ibm-configuration** + **assembly.execute** | Split assets: **api-spec**, **kind: api**, **policy-sequence**, Product/Plan |
| Assembly lived **on that API** | Policy sequence attaches globally, at API level, or at path/method scope |
| Visual canvas was the main path | **Form View** + **Code View**, project/Git workspace |

Same policies still matter — **invoke**, **map**, **switch**, security, transforms. Same Product / Plan / Catalog lifecycle. What changed is packaging: the flow is no longer trapped inside one API file.

## The one idea to keep

What used to be “the assembly on this API” is now something you attach:

**policy-seq** → **$ref: namespace:policy-sequence-name:version**

Same Orders flow (invoke backend → map response) still exists. In Designer it sat under **assembly.execute**. In Studio it lives in a **policy-sequence** file and the API just references it. Reuse without copy-paste.

### Old: one API YAML (Designer)

```yaml
# orders-1.0.0.yaml — contract + assembly together
openapi: 3.0.3
info:
  title: Orders API
  version: 1.0.0
paths:
  /orders/{orderId}:
    get:
      operationId: getOrder
      # ... parameters & responses ...

x-ibm-configuration:
  assembly:
    execute:
      - invoke:
          title: invoke-orders-backend
          target-url: $(orders-backend-url)/orders/$(request.parameters.orderId)
      - map:
          title: map-order-response
          inputs:
            backend:
              variable: message.body
          outputs:
            response:
              variable: message.body
          actions:
            - set: response.orderId
              from: backend.id
            - set: response.status
              from: backend.orderStatus
```

### New: api-spec + policy sequence (Studio)

**1. Clean OpenAPI (api-spec)**

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
      # ... parameters & responses ...
```

**2. Policy sequence (the old assembly)**

```yaml
# orders-backend-flow.yaml
kind: PolicySequence
metadata:
  name: orders-backend-flow
  namespace: orders
  version: 1.0.0
spec:
  execute:
    - invoke:
        title: invoke-orders-backend
        target-url: $(orders-backend-url)/orders/$(request.parameters.orderId)
    - map:
        title: map-order-response
        inputs:
          backend:
            variable: message.body
        outputs:
          response:
            variable: message.body
        actions:
          - set: response.orderId
            from: backend.id
          - set: response.status
            from: backend.orderStatus
```

**3. API metadata attaches it**

```yaml
# orders-api.yaml
kind: api
metadata:
  name: orders
  namespace: orders
  version: 1.0.0
spec:
  api-spec: orders-api-spec.yaml
  policy-seq:
    $ref: orders:orders-backend-flow:1.0.0
```

Same **invoke** → **map**. Different packaging — and that sequence can be reused on other APIs or scopes.

## Why IBM moved this way

Studio is built for **API-as-code**, shared governance, multi-gateway authoring (DataPower API Gateway, Nano, webMethods), DevOps ownership, and AI/agent tooling. Structured assets beat one opaque mega-YAML.

**Day-1 remap:** create a project → import OpenAPI → create a policy sequence → attach at API level → try scope-level → Tryout/publish. Start in Form View if you loved the canvas; switch to Code View once **$ref** clicks. And do not assume old v10 APIs drop unchanged onto Nano or webMethods.

---

If you are still asking “where did Assembly go?” — reframe it. Assembly became a reusable object. Designer is deprecated. The ideas are not.
