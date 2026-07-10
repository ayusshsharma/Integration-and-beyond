---
layout: post
title: "API Connect: How I Structure a Complete API Lifecycle"
date: 2025-11-15 09:00:00 +0400
categories: [IBM API Connect]
tags: [api-connect, lifecycle, openapi, ibm]
---

Managing APIs at enterprise scale requires more than just publishing endpoints. Over the years, I've developed a structured lifecycle approach using IBM API Connect that keeps design, security, and operations aligned.

## Lifecycle Overview

My API lifecycle has five phases: Design, Develop, Test, Publish, and Operate. Each phase has clear artifacts and gate criteria before moving forward.

## Design Phase

I start every API with an OpenAPI specification. This becomes the contract of record and feeds directly into API Connect's draft API creation.

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "Customer Orders API",
    "version": "1.0.0",
    "description": "Manage customer order lifecycle"
  },
  "servers": [
    { "url": "https://api.example.com/v1" }
  ],
  "paths": {
    "/orders": {
      "get": {
        "summary": "List orders",
        "operationId": "listOrders",
        "responses": {
          "200": {
            "description": "Successful response",
            "content": {
              "application/json": {
                "schema": {
                  "type": "array",
                  "items": { "$ref": "#/components/schemas/Order" }
                }
              }
            }
          }
        }
      }
    }
  }
}
```

## Catalog Configuration

Each environment gets its own catalog with isolated product subscriptions. I use YAML-based configuration for reproducibility.

```yaml
catalog:
  name: dev-catalog
  org: integration-lab
  products:
    - name: customer-orders-product
      apis:
        - customer-orders-api:1.0.0
      plans:
        - name: standard
          rate-limits:
            default: 1000/hour
        - name: premium
          rate-limits:
            default: 10000/hour
  gateways:
    - datacenter: primary-dc
      gateway-service: gw-primary
```

## Assembly Policies

Security policies are applied at the assembly level. JWT validation and rate limiting are non-negotiable for production APIs.

```xml
<assembly>
  <execute>
    <set-variable name="requestId" value="$(uuid())"/>
    <jwt-validate>
      <issuer>https://auth.example.com</issuer>
      <audience>customer-orders-api</audience>
    </jwt-validate>
    <rate-limit>
      <rate>100</rate>
      <unit>minute</unit>
    </rate-limit>
    <invoke url="https://backend.example.com/orders"/>
  </execute>
</assembly>
```

## Deployment via CLI

I automate deployments using the `apic` CLI to ensure consistency across environments.

```bash
# Login to API Manager
apic login --server https://api-manager.example.com --username admin --password "$APIC_PASSWORD"

# Publish API to catalog
apic catalogs:publish dev-catalog --source customer-orders-api_1.0.0.yaml

# Verify deployment status
apic products:list --catalog dev-catalog --format json
```

## Key Takeaways

- Always start with OpenAPI — it drives everything downstream.
- Separate catalogs per environment to prevent cross-contamination.
- Automate publishing with CLI scripts in your CI/CD pipeline.
- Monitor analytics dashboards from day one, not after go-live.
