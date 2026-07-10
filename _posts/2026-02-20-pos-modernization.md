---
layout: post
title: "Modernizing Legacy POS: My Approach to VAT and Billing"
date: 2026-02-20 11:00:00 +0400
categories: [POS Modernization]
tags: [pos, vat, billing, modernization]
---

Legacy POS systems often handle billing logic that's grown organically over decades. When modernizing, VAT calculation and invoice generation are among the trickiest areas to get right. Here's my structured approach.

## Assessment Phase

I map every tax rule, discount scenario, and rounding behavior in the legacy system before writing a single line of replacement code. Surprises live in edge cases.

## VAT Calculation Model

I model tax rules as configuration rather than hard-coded logic. This makes regulatory changes deployable without code releases.

```yaml
tax_rules:
  - region: AE
    currency: AED
    rates:
      - category: standard
        rate: 0.05
        applies_to: [goods, services]
      - category: zero-rated
        rate: 0.00
        applies_to: [exports, medical]
      - category: exempt
        rate: null
        applies_to: [financial_services]
  rounding:
    method: HALF_UP
    precision: 2
```

## Invoice Structure

Every invoice follows a consistent JSON schema that feeds both the POS display and the backend ERP integration.

```json
{
  "invoiceId": "INV-2026-004521",
  "storeId": "STORE-042",
  "terminalId": "POS-07",
  "timestamp": "2026-02-20T11:00:00+04:00",
  "lineItems": [
    {
      "sku": "PROD-1001",
      "description": "Wireless Headphones",
      "quantity": 1,
      "unitPrice": 299.00,
      "taxCategory": "standard",
      "taxRate": 0.05,
      "taxAmount": 14.95,
      "lineTotal": 313.95
    }
  ],
  "subtotal": 299.00,
  "totalTax": 14.95,
  "grandTotal": 313.95,
  "paymentMethod": "card",
  "vatNumber": "100123456700003"
}
```

## Integration Layer

The POS communicates with the billing service via a REST API. Legacy ERP receives invoices through an async message queue.

```xml
<InvoiceMessage xmlns="urn:pos:billing:v2">
  <Header>
    <MessageId>MSG-20260220-004521</MessageId>
    <SourceSystem>POS-MODERN</SourceSystem>
    <TargetSystem>ERP-SAP</TargetSystem>
    <Timestamp>2026-02-20T11:00:05+04:00</Timestamp>
  </Header>
  <Body>
    <InvoiceRef>INV-2026-004521</InvoiceRef>
    <Action>CREATE</Action>
    <PayloadFormat>JSON</PayloadFormat>
  </Body>
</InvoiceMessage>
```

## Migration Strategy

I run old and new systems in parallel for at least one billing cycle. Every invoice is reconciled line-by-line.

```bash
# Run parallel reconciliation
python reconcile_invoices.py \
  --legacy-export /data/legacy_feb2026.csv \
  --modern-export /data/modern_feb2026.csv \
  --tolerance 0.01 \
  --output /reports/reconciliation_feb2026.html
```

## Lessons Learned

- Document every rounding rule — "obvious" math differs between systems.
- Test refund and partial-return scenarios early.
- Keep tax configuration externalized and auditable.
- Build reconciliation tooling before go-live, not after.
