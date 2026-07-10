---
layout: post
title: "Kong Gateway Admin: My Setup, Plugins, and Best Practices"
date: 2025-12-02 10:30:00 +0400
categories: [Kong Gateway]
tags: [kong, gateway, plugins, api-gateway]
---

Kong Gateway has become my go-to API gateway for microservices architectures. Here's how I configure Kong Admin, which plugins I rely on, and the practices that keep production stable.

## Architecture Setup

I run Kong in DB-less mode for smaller deployments and with PostgreSQL for enterprise setups requiring Admin API persistence and clustering.

## Declarative Configuration

Everything is defined in a single YAML file that version-controls alongside application code.

```yaml
_format_version: "3.0"
services:
  - name: orders-service
    url: http://orders-backend:8080
    routes:
      - name: orders-route
        paths:
          - /api/v1/orders
        strip_path: false
    plugins:
      - name: rate-limiting
        config:
          minute: 100
          policy: local
      - name: jwt
        config:
          claims_to_verify:
            - exp
      - name: request-transformer
        config:
          add:
            headers:
              - "X-Gateway: kong"
```

## Essential Plugins

These are the plugins I enable on nearly every service:

| Plugin | Purpose | Notes |
|--------|---------|-------|
| rate-limiting | Protect backends | Use Redis policy in clusters |
| jwt | Token validation | Pair with key-auth for service accounts |
| prometheus | Metrics export | Scrape /metrics endpoint |
| cors | Browser access | Restrict origins in production |

## Admin API Commands

Quick CLI operations I use daily when debugging or hot-fixing routes.

```bash
# Apply declarative config
deck sync --kong-addr http://localhost:8001 --state kong.yaml

# List all services
curl -s http://localhost:8001/services | jq '.data[].name'

# Add a consumer with JWT credentials
curl -X POST http://localhost:8001/consumers \
  -d "username=mobile-app"

curl -X POST http://localhost:8001/consumers/mobile-app/jwt \
  -H "Content-Type: application/json" \
  -d '{"key": "mobile-app-key", "secret": "super-secret"}'
```

## Health Check Configuration

```json
{
  "upstream": {
    "name": "orders-upstream",
    "healthchecks": {
      "active": {
        "type": "http",
        "http_path": "/health",
        "healthy": { "interval": 5, "successes": 2 },
        "unhealthy": { "interval": 3, "http_failures": 3 }
      }
    },
    "targets": [
      { "target": "orders-1:8080", "weight": 100 },
      { "target": "orders-2:8080", "weight": 100 }
    ]
  }
}
```

## Best Practices

- Never modify production via Admin API directly — use `deck` sync from Git.
- Enable Prometheus plugin globally for observability.
- Use separate Kong workspaces per team or domain.
- Test plugin ordering — JWT must run before rate-limiting in most cases.
