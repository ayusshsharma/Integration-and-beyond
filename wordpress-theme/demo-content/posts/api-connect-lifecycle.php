<?php
/**
 * Demo post: API Connect Lifecycle
 *
 * @package Ayush_Integration_Lab
 */

return array(
	'title'    => 'API Connect: How I Structure a Complete API Lifecycle',
	'category' => 'IBM API Connect',
	'date'     => '2025-11-15 09:00:00',
	'tags'     => array( 'api-connect', 'lifecycle', 'openapi', 'ibm' ),
	'content'  => <<<'HTML'
<p>Managing APIs at enterprise scale requires more than just publishing endpoints. Over the years, I've developed a structured lifecycle approach using IBM API Connect that keeps design, security, and operations aligned.</p>

<h2 id="lifecycle-overview">Lifecycle Overview</h2>
<p>My API lifecycle has five phases: Design, Develop, Test, Publish, and Operate. Each phase has clear artifacts and gate criteria before moving forward.</p>

<h2 id="design-phase">Design Phase</h2>
<p>I start every API with an OpenAPI specification. This becomes the contract of record and feeds directly into API Connect's draft API creation.</p>

<pre><code class="language-json">{
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
}</code></pre>

<h2 id="catalog-configuration">Catalog Configuration</h2>
<p>Each environment gets its own catalog with isolated product subscriptions. I use YAML-based configuration for reproducibility.</p>

<pre><code class="language-yaml">catalog:
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
      gateway-service: gw-primary</code></pre>

<h2 id="assembly-policies">Assembly Policies</h2>
<p>Security policies are applied at the assembly level. JWT validation and rate limiting are non-negotiable for production APIs.</p>

<pre><code class="language-xml">&lt;assembly&gt;
  &lt;execute&gt;
    &lt;set-variable name="requestId" value="$(uuid())"/&gt;
    &lt;jwt-validate&gt;
      &lt;issuer&gt;https://auth.example.com&lt;/issuer&gt;
      &lt;audience&gt;customer-orders-api&lt;/audience&gt;
    &lt;/jwt-validate&gt;
    &lt;rate-limit&gt;
      &lt;rate&gt;100&lt;/rate&gt;
      &lt;unit&gt;minute&lt;/unit&gt;
    &lt;/rate-limit&gt;
    &lt;invoke url="https://backend.example.com/orders"/&gt;
  &lt;/execute&gt;
&lt;/assembly&gt;</code></pre>

<h2 id="deployment-cli">Deployment via CLI</h2>
<p>I automate deployments using the <code>apic</code> CLI to ensure consistency across environments.</p>

<pre><code class="language-bash"># Login to API Manager
apic login --server https://api-manager.example.com --username admin --password "$APIC_PASSWORD"

# Publish API to catalog
apic catalogs:publish dev-catalog --source customer-orders-api_1.0.0.yaml

# Verify deployment status
apic products:list --catalog dev-catalog --format json</code></pre>

<h2 id="key-takeaways">Key Takeaways</h2>
<ul>
<li>Always start with OpenAPI — it drives everything downstream.</li>
<li>Separate catalogs per environment to prevent cross-contamination.</li>
<li>Automate publishing with CLI scripts in your CI/CD pipeline.</li>
<li>Monitor analytics dashboards from day one, not after go-live.</li>
</ul>
HTML,
);
