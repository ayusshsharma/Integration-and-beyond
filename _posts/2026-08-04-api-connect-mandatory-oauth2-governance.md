---
layout: post
title: "API Governance in IBM API Connect: Create Rules and Test Against an API"
date: 2026-08-04 12:00:00 +0400
description: "Build a custom mybank-oauth-check ruleset in IBM API Connect Governance, publish the rules, and validate them against an API to catch OAuth2 compliance failures."
categories: [IBM API Connect]
tags: [api-connect, governance, oauth2, security, rulesets, validation, ibm]
---

IBM API Connect Governance turns security policy into machine-checkable rules. This walkthrough creates a custom OAuth2 ruleset, selects those rules in a validation scan, and runs them against an API so non-compliant definitions fail before publish.

<!--more-->

## What We Are Enforcing

The **mybank-oauth-check** ruleset requires every API to:

- Use an **OAuth2** security definition (**type** must match **oauth2**)
- Reference the approved myBank provider via **x-ibm-oauth-provider**
- Declare at least one explicit **scope**
- Never use the **implicit** grant
- Never clear security at the operation level with an empty array

## Step 1 — Create the Governance Ruleset

In the provider org, open **API Governance → Rulesets** and create a new ruleset named **mybank-oauth-check** (version **1.0.0**). Paste the rules below, or import the YAML as a ruleset file.

```yaml
name: mybank-oauth-check
title: myBank-oauth-check
description: ""
ruleset_version: 1.0.0

rules:
  - title: no-implicit-grant-type
    id: 4cf01579-a7f6-4ac2-8bf9-7ab41da14162
    name: no-implicit-grant-type
    version: 1.0.0
    description: Implicit grant is disallowed org-wide.
    given:
      $.securityDefinitions[?(@.type=='oauth2')]
    severity: error
    then:
      - function: schema
        functionOptions:
          schema:
            type: object
            required:
              - flow
            properties:
              flow:
                not:
                  enum:
                    - implicit
                type: string

  - title: no-operation-level-security-bypass
    id: 85d45013-144e-4bbb-915e-b253a505512a
    name: no-operation-level-security-bypass
    version: 1.0.0
    description: No operation may override the API-level requirement with an empty security array.
    given:
      $.paths[*][*].security
    severity: error
    then:
      - function: schema
        functionOptions:
          schema:
            type: array
            minItems: 1

  - title: oauth-provider-registered
    id: 94fcbbe3-eac5-4191-b63d-2778a85b874b
    name: oauth-provider-registered
    version: 1.0.0
    description: Every security definition must reference the approved myBank OAuth2 provider.
    given:
      $.securityDefinitions[*]
    severity: error
    then:
      function: schema
      functionOptions:
        schema:
          type: object
          required:
            - x-ibm-oauth-provider
          properties:
            x-ibm-oauth-provider:
              type: string
              pattern: ^inEdgeOAuth2Provider-\d+\.\d+\.\d+-[a-f0-9]{4}$

  - title: oauth-required-scopes-defined
    id: 689ad9c9-d3e5-4c23-88d2-346d8d808e03
    name: oauth-required-scopes-defined
    version: 1.0.0
    description: Every OAuth2 security definition must declare at least one explicit scope.
    given:
      $.securityDefinitions[*]
    severity: error
    then:
      field: scopes
      function: schema
      functionOptions:
        schema:
          type: object
          minProperties: 1

  - title: oauth-security-definition
    id: 84743e6e-3064-4384-88f0-d1ef2d4766a
    name: oauth-security-definition
    version: 1.0.0
    description: OAuth2 Provider not defined.
    given:
      $.securityDefinitions[*]
    severity: error
    then:
      field: type
      function: pattern
      functionOptions:
        match: ^oauth2$
```

Save and publish the ruleset so it becomes available for validation scans in the provider org.

## Step 2 — Select the Rules for Validation

Start **Validate APIs with ruleset**. On **Select rules**, choose the five rules from **mybank-oauth-check** — all of them in this example.

![Select rules screen for mybank-oauth-check]({{ '/assets/images/posts/api-connect-oauth2-governance/rule-selection-screen.jpg' | relative_url }})

| Rule | What it catches |
|------|-----------------|
| **no-implicit-grant-type** | OAuth2 **flow: implicit** |
| **no-operation-level-security-bypass** | Operation **security: []** that strips API-level auth |
| **oauth-provider-registered** | Missing or invalid **x-ibm-oauth-provider** |
| **oauth-required-scopes-defined** | Empty or missing **scopes** |
| **oauth-security-definition** | Security definition **type** not **oauth2** |

Continue to **Select APIs** and pick the API you want to test — here, **balance-transfer-api:1.0.0**.

## Step 3 — Run the Scan and Read the Results

Open **View results**. Against a non-compliant definition, the scorecard returns **error**-level findings tied to specific rules.

![Validation results for balance-transfer-api against mybank-oauth-check]({{ '/assets/images/posts/api-connect-oauth2-governance/scorecard-failing.jpg' | relative_url }})

In this run, **balance-transfer-api:1.0.0** fails three rules in **mybank-oauth-check:1.0.0**:

1. **oauth-provider-registered** — no approved myBank OAuth2 provider reference
2. **oauth-required-scopes-defined** — no explicit scopes declared
3. **oauth-security-definition** — OAuth2 provider / type not defined as required

Each row tells you the severity, message, rule name, ruleset version, and API — enough to fix the OpenAPI definition without guessing.

## Step 4 — Fix the API and Re-test

Update the API definition so it satisfies every rule, then re-run the same validation:

1. Add a security definition with **"type": "oauth2"**.
2. Set **x-ibm-oauth-provider** to a value matching the approved provider id (for example **inEdgeOAuth2Provider-1.0.0-a1b2**).
3. Declare at least one scope under **scopes**.
4. Use a non-implicit flow (for example **accessCode** / authorization code).
5. Remove any operation-level **security: []** overrides.

Re-validate with the same five rules selected. A clean scorecard means the contract now meets org OAuth2 policy and is ready for the next publish gate.

## Key Takeaways

- A ruleset encodes OAuth2 policy once; every scan reuses the same checks.
- **Select rules → Select APIs → View results** is the fastest loop for testing a new ruleset against a real API.
- Error messages map 1:1 to rule names, so fixes are specific (**x-ibm-oauth-provider**, scopes, type, flow, operation security).
- Catching failures on **balance-transfer-api** before publish keeps non-compliant definitions out of the catalog.
