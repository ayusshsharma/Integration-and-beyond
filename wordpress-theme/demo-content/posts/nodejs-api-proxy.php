<?php
/**
 * Demo post: Node.js API Proxy POC
 *
 * @package Ayush_Integration_Lab
 */

return array(
	'title'    => 'Experiment: Building a Simple API Proxy Using Node.js',
	'category' => 'Tech Experiments / POCs',
	'date'     => '2026-04-01 16:30:00',
	'tags'     => array( 'nodejs', 'proxy', 'poc', 'experiment' ),
	'content'  => <<<'HTML'
<p>Sometimes you need a lightweight proxy faster than deploying a full API gateway. This POC explores a minimal Node.js proxy with logging, header injection, and basic auth forwarding — useful for dev environments and quick integrations.</p>

<h2 id="project-structure">Project Structure</h2>
<pre><code class="language-bash">api-proxy-poc/
├── package.json
├── src/
│   ├── index.js
│   ├── proxy.js
│   └── middleware/
│       ├── logger.js
│       └── auth.js
├── config/
│   └── routes.yaml
└── Dockerfile</code></pre>

<h2 id="route-configuration">Route Configuration</h2>
<p>Routes are defined in YAML so non-developers can adjust mappings without touching code.</p>

<pre><code class="language-yaml">server:
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
      forward: true</code></pre>

<h2 id="proxy-implementation">Proxy Implementation</h2>
<pre><code class="language-javascript">const express = require('express');
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
    changeOrigin: true,
    onProxyReq: (proxyReq, req) => {
      if (route.headers?.add) {
        Object.entries(route.headers.add).forEach(([key, val]) => {
          proxyReq.setHeader(key, val.replace('{{timestamp}}', new Date().toISOString()));
        });
      }
    }
  }));
});

app.listen(config.server.port, config.server.host, () => {
  console.log(`Proxy running on port ${config.server.port}`);
});</code></pre>

<h2 id="sample-request-response">Sample Request / Response</h2>
<pre><code class="language-bash"># Send a test request through the proxy
curl -X GET http://localhost:3000/api/v1/orders/12345 \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIs..." \
  -H "Accept: application/json" \
  -v</code></pre>

<pre><code class="language-json">{
  "request": {
    "method": "GET",
    "path": "/api/v1/orders/12345",
    "headers": {
      "Authorization": "Bearer eyJ...",
      "X-Proxy-Source": "node-proxy-poc",
      "X-Request-Time": "2026-04-01T16:30:00.000Z"
    }
  },
  "response": {
    "status": 200,
    "body": {
      "orderId": "12345",
      "status": "shipped",
      "items": 3
    },
    "latencyMs": 42
  }
}</code></pre>

<h2 id="docker-deployment">Docker Deployment</h2>
<pre><code class="language-bash"># Build and run
docker build -t api-proxy-poc .
docker run -d -p 3000:3000 --name proxy api-proxy-poc

# Health check
curl http://localhost:3000/health</code></pre>

<h2 id="conclusion">Conclusion</h2>
<p>This POC is not a replacement for Kong or API Connect in production. But for dev environments, demos, and quick integration tests, a 50-line Node.js proxy gets you moving in minutes. The full code is available in my GitHub experiments repo.</p>
HTML,
);
