<?php
/**
 * Demo post: POS Modernization
 *
 * @package Ayush_Integration_Lab
 */

return array(
	'title'    => 'Modernizing Legacy POS: My Approach to VAT and Billing',
	'category' => 'POS Modernization',
	'date'     => '2026-02-20 11:00:00',
	'tags'     => array( 'pos', 'vat', 'billing', 'modernization' ),
	'content'  => <<<'HTML'
<p>Legacy POS systems often handle billing logic that's grown organically over decades. When modernizing, VAT calculation and invoice generation are among the trickiest areas to get right. Here's my structured approach.</p>

<h2 id="assessment-phase">Assessment Phase</h2>
<p>I map every tax rule, discount scenario, and rounding behavior in the legacy system before writing a single line of replacement code. Surprises live in edge cases.</p>

<h2 id="vat-calculation-model">VAT Calculation Model</h2>
<p>I model tax rules as configuration rather than hard-coded logic. This makes regulatory changes deployable without code releases.</p>

<pre><code class="language-yaml">tax_rules:
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
    precision: 2</code></pre>

<h2 id="invoice-structure">Invoice Structure</h2>
<p>Every invoice follows a consistent JSON schema that feeds both the POS display and the backend ERP integration.</p>

<pre><code class="language-json">{
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
}</code></pre>

<h2 id="integration-layer">Integration Layer</h2>
<p>The POS communicates with the billing service via a REST API. Legacy ERP receives invoices through an async message queue.</p>

<pre><code class="language-xml">&lt;InvoiceMessage xmlns="urn:pos:billing:v2"&gt;
  &lt;Header&gt;
    &lt;MessageId&gt;MSG-20260220-004521&lt;/MessageId&gt;
    &lt;SourceSystem&gt;POS-MODERN&lt;/SourceSystem&gt;
    &lt;TargetSystem&gt;ERP-SAP&lt;/TargetSystem&gt;
    &lt;Timestamp&gt;2026-02-20T11:00:05+04:00&lt;/Timestamp&gt;
  &lt;/Header&gt;
  &lt;Body&gt;
    &lt;InvoiceRef&gt;INV-2026-004521&lt;/InvoiceRef&gt;
    &lt;Action&gt;CREATE&lt;/Action&gt;
    &lt;PayloadFormat&gt;JSON&lt;/PayloadFormat&gt;
  &lt;/Body&gt;
&lt;/InvoiceMessage&gt;</code></pre>

<h2 id="migration-strategy">Migration Strategy</h2>
<p>I run old and new systems in parallel for at least one billing cycle. Every invoice is reconciled line-by-line.</p>

<pre><code class="language-bash"># Run parallel reconciliation
python reconcile_invoices.py \
  --legacy-export /data/legacy_feb2026.csv \
  --modern-export /data/modern_feb2026.csv \
  --tolerance 0.01 \
  --output /reports/reconciliation_feb2026.html</code></pre>

<h2 id="lessons-learned">Lessons Learned</h2>
<ul>
<li>Document every rounding rule — "obvious" math differs between systems.</li>
<li>Test refund and partial-return scenarios early.</li>
<li>Keep tax configuration externalized and auditable.</li>
<li>Build reconciliation tooling before go-live, not after.</li>
</ul>
HTML,
);
