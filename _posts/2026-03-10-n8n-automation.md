---
layout: post
title: "n8n: My First Automation Workflow for Daily Tech Tasks"
date: 2026-03-10 08:45:00 +0400
categories: [n8n Automation]
tags: [n8n, automation, workflow, productivity]
---

After years of writing custom scripts for repetitive tasks, I finally tried n8n. My first workflow automates the daily tech routine: checking API health, summarizing logs, and posting status to Slack. Here's how I built it.

## Workflow Overview

The workflow runs every morning at 8 AM. It hits health endpoints, parses responses, aggregates results, and sends a formatted summary to a Slack channel.

## Workflow Definition

Exporting workflows as JSON makes them version-controllable and portable across n8n instances.

```json
{
  "name": "Daily API Health Check",
  "nodes": [
    {
      "name": "Schedule Trigger",
      "type": "n8n-nodes-base.scheduleTrigger",
      "parameters": {
        "rule": {
          "interval": [{ "field": "cronExpression", "expression": "0 8 * * 1-5" }]
        }
      }
    },
    {
      "name": "Check Kong Gateway",
      "type": "n8n-nodes-base.httpRequest",
      "parameters": {
        "url": "http://kong:8001/status",
        "method": "GET"
      }
    },
    {
      "name": "Post to Slack",
      "type": "n8n-nodes-base.slack",
      "parameters": {
        "channel": "#integration-status"
      }
    }
  ]
}
```

## Docker Setup

I self-host n8n using Docker Compose with persistent volumes for workflow data.

```yaml
version: "3.8"
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
  n8n_data:
```

## CLI Operations

```bash
# Start n8n stack
docker compose up -d

# Export workflow for backup
curl -u admin:$N8N_PASSWORD \
  http://localhost:5678/api/v1/workflows/1 \
  | jq '.' > daily-health-check.json

# Import workflow to another instance
curl -u admin:$N8N_PASSWORD \
  -X POST http://localhost:5678/api/v1/workflows \
  -H "Content-Type: application/json" \
  -d @daily-health-check.json
```

## Next Steps

- Add error handling nodes with retry logic for flaky endpoints.
- Integrate with PagerDuty for critical failures.
- Build a weekly report workflow aggregating seven days of health data.
