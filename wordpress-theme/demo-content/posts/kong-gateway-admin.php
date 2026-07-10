<?php
/**
 * Demo post: Kong Gateway Admin
 *
 * @package Ayush_Integration_Lab
 */

return array(
	'title'    => 'Kong Gateway Admin: My Setup, Plugins, and Best Practices',
	'category' => 'Kong Gateway',
	'date'     => '2025-12-02 10:30:00',
	'tags'     => array( 'kong', 'gateway', 'plugins', 'api-gateway' ),
	'content'  => <<<'HTML'
<p>Kong Gateway has become my go-to API gateway for microservices architectures. Here's how I configure Kong Admin, which plugins I rely on, and the practices that keep production stable.</p>

<h2 id="architecture-setup">Architecture Setup</h2>
<p>I run Kong in DB-less mode for smaller deployments and with PostgreSQL for enterprise setups requiring Admin API persistence and clustering.</p>

<h2 id="declarative-config">Declarative Configuration</h2>
<p>Everything is defined in a single YAML file that version-controls alongside application code.</p>

<pre><code class="language-yaml">_format_version: "3.0"
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
              - "X-Gateway: kong"</code></pre>

<h2 id="essential-plugins">Essential Plugins</h2>
<p>These are the plugins I enable on nearly every service:</p>

<table>
<thead>
<tr><th>Plugin</th><th>Purpose</th><th>Notes</th></tr>
</thead>
<tbody>
<tr><td>rate-limiting</td><td>Protect backends</td><td>Use Redis policy in clusters</td></tr>
<tr><td>jwt</td><td>Token validation</td><td>Pair with key-auth for service accounts</td></tr>
<tr><td>prometheus</td><td>Metrics export</td><td>Scrape /metrics endpoint</td></tr>
<tr><td>cors</td><td>Browser access</td><td>Restrict origins in production</td></tr>
</tbody>
</table>

<h2 id="admin-api-commands">Admin API Commands</h2>
<p>Quick CLI operations I use daily when debugging or hot-fixing routes.</p>

<pre><code class="language-bash"># Apply declarative config
deck sync --kong-addr http://localhost:8001 --state kong.yaml

# List all services
curl -s http://localhost:8001/services | jq '.data[].name'

# Add a consumer with JWT credentials
curl -X POST http://localhost:8001/consumers \
  -d "username=mobile-app"

curl -X POST http://localhost:8001/consumers/mobile-app/jwt \
  -H "Content-Type: application/json" \
  -d '{"key": "mobile-app-key", "secret": "super-secret"}'</code></pre>

<h2 id="health-check-config">Health Check Configuration</h2>
<pre><code class="language-json">{
  "upstream": {
    "name": "orders-upstream",
    "healthchecks": {
      "active": {
        "type": "http",
        "http_path": "/health",
        "healthy": {
          "interval": 5,
          "successes": 2
        },
        "unhealthy": {
          "interval": 3,
          "http_failures": 3
        }
      }
    },
    "targets": [
      { "target": "orders-1:8080", "weight": 100 },
      { "target": "orders-2:8080", "weight": 100 }
    ]
  }
}</code></pre>

<h2 id="best-practices">Best Practices</h2>
<ul>
<li>Never modify production via Admin API directly — use <code>deck</code> sync from Git.</li>
<li>Enable Prometheus plugin globally for observability.</li>
<li>Use separate Kong workspaces per team or domain.</li>
<li>Test plugin ordering — JWT must run before rate-limiting in most cases.</li>
</ul>
HTML,
);
