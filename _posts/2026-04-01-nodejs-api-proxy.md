---
layout: post
title: "Experiment: Building a Simple API Proxy Using Node.js"
date: 2026-04-01 16:30:00 +0400
categories: [Tech Experiments / POCs]
tags: [nodejs, proxy, poc, experiment]
---

Sometimes you need a lightweight proxy faster than deploying a full API gateway. This POC explores a minimal Node.js proxy with logging, header injection, and basic auth forwarding — useful for dev environments and quick integrations.

## Project Structure

```bash
api-proxy-poc/
├── package.json
├── src/
│   ├── index.js
│   ├── proxy.js
│   └── middleware/
│       ├── logger.js
│       └── auth.js
├── config/
│   └── routes.yaml
└── Dockerfile
```

## Route Configuration

Routes are defined in YAML so non-developers can adjust mappings without touching code.

```yaml
server:
  port: 3000
  host: 0.0.0.0

routes:
  - path: /api/v1/orders/*
    target: http://orders-service:8080
    stripPrefix: false
    headers:
      add:
        X-Proxy-Source: node-proxy-poc
        X-Request-Time: "{{timestamp}}"
  - path: /api/v1/customers/*
    target: http://customers-service:8080
    stripPrefix: false
    auth:
      type: bearer
      forward: true
```

## Proxy Implementation

```javascript
const express = require('express');
const { createProxyMiddleware } = require('http-proxy-middleware');
const yaml = require('js-yaml');
const fs = require('fs');

const config = yaml.load(fs.readFileSync('./config/routes.yaml', 'utf8'));
const app = express();

app.use((req, res, next) => {
  console.log(JSON.stringify({
    timestamp: new Date().toISOString(),
    method: req.method,
    path: req.path,
    ip: req.ip
  }));
  next();
});

config.routes.forEach(route => {
  app.use(route.path, createProxyMiddleware({
    target: route.target,
    changeOrigin: true
  }));
});

app.listen(config.server.port, config.server.host, () => {
  console.log(`Proxy running on port ${config.server.port}`);
});
```

## Sample Request / Response

```bash
curl -X GET http://localhost:3000/api/v1/orders/12345 \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIs..." \
  -H "Accept: application/json" \
  -v
```

```json
{
  "request": {
    "method": "GET",
    "path": "/api/v1/orders/12345",
    "headers": {
      "Authorization": "Bearer eyJ...",
      "X-Proxy-Source": "node-proxy-poc"
    }
  },
  "response": {
    "status": 200,
    "body": { "orderId": "12345", "status": "shipped", "items": 3 },
    "latencyMs": 42
  }
}
```

## Docker Deployment

```bash
docker build -t api-proxy-poc .
docker run -d -p 3000:3000 --name proxy api-proxy-poc
curl http://localhost:3000/health
```

## Conclusion

This POC is not a replacement for Kong or API Connect in production. But for dev environments, demos, and quick integration tests, a 50-line Node.js proxy gets you moving in minutes.
