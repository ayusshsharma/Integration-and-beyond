<?php
/**
 * Demo post: n8n Automation
 *
 * @package Ayush_Integration_Lab
 */

return array(
	'title'    => 'n8n: My First Automation Workflow for Daily Tech Tasks',
	'category' => 'n8n Automation',
	'date'     => '2026-03-10 08:45:00',
	'tags'     => array( 'n8n', 'automation', 'workflow', 'productivity' ),
	'content'  => <<<'HTML'
<p>After years of writing custom scripts for repetitive tasks, I finally tried n8n. My first workflow automates the daily tech routine: checking API health, summarizing logs, and posting status to Slack. Here's how I built it.</p>

<h2 id="workflow-overview">Workflow Overview</h2>
<p>The workflow runs every morning at 8 AM. It hits health endpoints, parses responses, aggregates results, and sends a formatted summary to a Slack channel.</p>

<h2 id="workflow-definition">Workflow Definition</h2>
<p>Exporting workflows as JSON makes them version-controllable and portable across n8n instances.</p>

<pre><code class="language-json">{
  "name": "Daily API Health Check",
  "nodes": [
    {
      "name": "Schedule Trigger",
      "type": "n8n-nodes-base.scheduleTrigger",
      "parameters": {
        "rule": {
          "interval": [{ "field": "cronExpression", "expression": "0 8 * * 1-5" }]
        }
      },
      "position": [250, 300]
    },
    {
      "name": "Check Kong Gateway",
      "type": "n8n-nodes-base.httpRequest",
      "parameters": {
        "url": "http://kong:8001/status",
        "method": "GET",
        "options": { "timeout": 5000 }
      },
      "position": [500, 200]
    },
    {
      "name": "Check API Connect",
      "type": "n8n-nodes-base.httpRequest",
      "parameters": {
        "url": "https://api-manager.example.com/api/health",
        "method": "GET",
        "authentication": "genericCredentialType"
      },
      "position": [500, 400]
    },
    {
      "name": "Format Summary",
      "type": "n8n-nodes-base.code",
      "parameters": {
        "jsCode": "const results = items.map(i => ({ service: i.json.service, status: i.json.status })); return [{ json: { summary: results, timestamp: new Date().toISOString() } }];"
      },
      "position": [750, 300]
    },
    {
      "name": "Post to Slack",
      "type": "n8n-nodes-base.slack",
      "parameters": {
        "channel": "#integration-status",
        "text": "={{ $json.summary }}"
      },
      "position": [1000, 300]
    }
  ],
  "connections": {
    "Schedule Trigger": { "main": [[{ "node": "Check Kong Gateway" }, { "node": "Check API Connect" }]] },
    "Check Kong Gateway": { "main": [[{ "node": "Format Summary" }]] },
    "Check API Connect": { "main": [[{ "node": "Format Summary" }]] },
    "Format Summary": { "main": [[{ "node": "Post to Slack" }]] }
  }
}</code></pre>

<h2 id="docker-setup">Docker Setup</h2>
<p>I self-host n8n using Docker Compose with persistent volumes for workflow data.</p>

<pre><code class="language-yaml">version: "3.8"
services:
  n8n:
    image: n8nio/n8n:latest
    ports:
      - "5678:5678"
    environment:
      - N8N_BASIC_AUTH_ACTIVE=true
      - N8N_BASIC_AUTH_USER=admin
      - N8N_BASIC_AUTH_PASSWORD=${N8N_PASSWORD}
      - GENERIC_TIMEZONE=Asia/Dubai
    volumes:
      - n8n_data:/home/node/.n8n
    restart: unless-stopped

volumes:
  n8n_data:</code></pre>

<h2 id="cli-operations">CLI Operations</h2>
<pre><code class="language-bash"># Start n8n stack
docker compose up -d

# Export workflow for backup
curl -u admin:$N8N_PASSWORD \
  http://localhost:5678/api/v1/workflows/1 \
  | jq '.' > daily-health-check.json

# Import workflow to another instance
curl -u admin:$N8N_PASSWORD \
  -X POST http://localhost:5678/api/v1/workflows \
  -H "Content-Type: application/json" \
  -d @daily-health-check.json</code></pre>

<h2 id="next-steps">Next Steps</h2>
<ul>
<li>Add error handling nodes with retry logic for flaky endpoints.</li>
<li>Integrate with PagerDuty for critical failures.</li>
<li>Build a weekly report workflow aggregating seven days of health data.</li>
</ul>
HTML,
);
