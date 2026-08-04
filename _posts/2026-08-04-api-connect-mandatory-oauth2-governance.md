---
layout: post
title: "Enforcing Mandatory OAuth2 Governance in IBM API Connect"
date: 2026-08-04 09:00:00 +0400
description: "Make OAuth2 mandatory across a provider org in IBM API Connect using the Governance service — rulesets, validation scans, and scorecards — plus runtime enforcement and CI/CD checks."
categories: [IBM API Connect]
tags: [api-connect, governance, oauth2, security, rulesets, scorecard, ci-cd, ibm]
---

IBM API Connect Governance lets you codify API security policy as rulesets and enforce it before a Product is published. This post shows how to make OAuth2 mandatory across a provider org using a ruleset, validation scans, and scorecards, then back it with runtime enforcement and a CI/CD gate.

<!--more-->

## Step 1 — Enable API Connect Governance

Governance is an add-on available from API Connect **v10.0.6** onward. Enable it on the management subsystem so the Governance service — rulesets, scans, and scorecards — becomes available in the provider org.

## Step 2 — Define the OAuth2 governance ruleset

Write a ruleset that makes an OAuth2 security definition mandatory, requires it to reference a registered OAuth2 provider, requires scopes, disallows the implicit grant, and blocks operation-level security overrides. Use your exact ruleset YAML:

```yaml
# [paste ruleset YAML here]
```

## Step 3 — Publish the ruleset to the provider org

Publish the ruleset with the `apic` CLI so it becomes selectable when configuring a scan.

```bash
# [paste apic commands here]
```

![Rule selection screen]({{ '/assets/images/posts/api-connect-oauth2-governance/rule-selection-screen.png' | relative_url }})
<!-- [IMAGE: rule-selection-screen] — add rule-selection-screen.png at the path above -->

## Step 4 — Run a validation scan (failing case)

Run the ruleset against a sample API and open the scorecard. In this scan, `balance-transfer-api:1.0.0` fails against `mybank-oauth-check:1.0.0` because it has no registered OAuth2 provider reference and no OAuth2 provider defined.

![Scorecard — failing]({{ '/assets/images/posts/api-connect-oauth2-governance/scorecard-failing.png' | relative_url }})
<!-- [IMAGE: scorecard-failing] — add scorecard-failing.png at the path above -->

## Step 5 — Fix the API definition and re-validate

Add a mandatory OAuth2 security definition that references the registered provider, declare the required scopes, remove the implicit grant, and drop any operation-level security overrides. Re-run the same scan; the scorecard clears every error.

## Step 6 — Add runtime enforcement via a gateway policy

A clean scorecard confirms the definition is correct, but the gateway must still validate the token at request time. Add an OAuth/JWT policy to the assembly to verify the access token and required scopes on every call.

```yaml
# [CODE: gateway-oauth-policy] — paste policy YAML/JSON here
```

## Step 7 — Gate Product publishing on a clean scorecard

Require a passing scorecard as an approval step before a Product moves to the production catalog, so a non-compliant API cannot be published.

## Step 8 — Automate the scan in CI/CD

Run the same scan in the pipeline on every change and fail the build on scorecard errors, keeping enforcement continuous instead of manual.

```bash
# Run the published ruleset scan in CI and fail on errors
# [paste apic scan command here]
```

## Key Takeaways

- Rulesets turn OAuth2 requirements into machine-checkable policy across the whole provider org.
- Scans and scorecards catch missing providers, missing scopes, implicit grants, and operation-level bypasses before publish.
- Design-time governance and a gateway OAuth/JWT policy are complementary: the scorecard validates the contract, the gateway enforces the token.
- Gating publish on a clean scorecard and running the scan in CI/CD make compliance mandatory, not optional.
