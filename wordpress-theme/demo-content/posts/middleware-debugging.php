<?php
/**
 * Demo post: Middleware Debugging
 *
 * @package Ayush_Integration_Lab
 */

return array(
	'title'    => 'How I Debug Middleware Issues in Large Enterprises',
	'category' => 'Middleware & Integration',
	'date'     => '2026-01-08 14:00:00',
	'tags'     => array( 'middleware', 'debugging', 'integration', 'enterprise' ),
	'content'  => <<<'HTML'
<p>Enterprise middleware failures rarely announce themselves clearly. Messages get lost, transformations fail silently, and logs are scattered across a dozen systems. This is my systematic approach to finding root cause.</p>

<h2 id="symptom-classification">Symptom Classification</h2>
<p>Before touching any server, I classify the failure: connectivity, transformation, sequencing, or data quality. Each category has different diagnostic tools.</p>

<h2 id="message-flow-tracing">Message Flow Tracing</h2>
<p>I enable correlation IDs at the entry point and propagate them through every hop. This single practice saves hours of log correlation.</p>

<pre><code class="language-xml">&lt;messageFlow name="OrderProcessingFlow"&gt;
  &lt;nodes&gt;
    &lt;node name="HTTP Input" type="HTTPInput"&gt;
      &lt;properties&gt;
        &lt;property name="setCorrelationId" value="true"/&gt;
      &lt;/properties&gt;
    &lt;/node&gt;
    &lt;node name="Transform Order" type="Compute"&gt;
      &lt;properties&gt;
        &lt;property name="computeExpression" value="ESQL OrderTransform.esql"/&gt;
      &lt;/properties&gt;
    &lt;/node&gt;
    &lt;node name="MQ Output" type="MQOutput"/&gt;
  &lt;/nodes&gt;
&lt;/messageFlow&gt;</code></pre>

<h2 id="log-analysis-commands">Log Analysis Commands</h2>
<p>On Linux-based integration nodes, these commands are my first line of investigation.</p>

<pre><code class="language-bash"># Find all errors in the last hour with correlation ID
grep -i "ERROR" /var/mqsi/log/integration-server.log \
  | grep "CID-abc123" \
  | tail -50

# Check queue depths
echo "DISPLAY QLOCAL(ORDER.IN)" | runmqsc QMgr01

# Trace a specific message flow
mqsichangeflowmsg UserTrace ON -i integration-server -k OrderProcessingFlow
mqsireportmsgflows integration-server -e OrderProcessingFlow -c active</code></pre>

<h2 id="payload-inspection">Payload Inspection</h2>
<p>When transformation is the suspect, I capture the before and after payloads using a trace node or temporary logging compute node.</p>

<pre><code class="language-json">{
  "correlationId": "CID-abc123",
  "timestamp": "2026-01-08T10:15:30Z",
  "stage": "post-transform",
  "payload": {
    "orderId": "ORD-98765",
    "customerId": "CUST-4421",
    "lineItems": [
      { "sku": "SKU-001", "quantity": 2, "unitPrice": 29.99 }
    ],
    "totalAmount": 59.98,
    "currency": "USD"
  }
}</code></pre>

<h2 id="common-pitfalls">Common Pitfalls</h2>
<ul>
<li><strong>Character encoding mismatches</strong> — UTF-8 vs ISO-8859-1 causes silent data corruption.</li>
<li><strong>Schema version drift</strong> — Producer updated XSD but consumer still validates against v1.</li>
<li><strong>Timeout cascades</strong> — One slow backend causes queue buildup and message expiry.</li>
<li><strong>Missing dead letter queues</strong> — Failed messages disappear instead of being recoverable.</li>
</ul>

<h2 id="debug-checklist">My Debug Checklist</h2>
<ol>
<li>Reproduce with a known-good test message.</li>
<li>Trace correlation ID end-to-end.</li>
<li>Compare payload at each transformation stage.</li>
<li>Check queue depths and backout counts.</li>
<li>Review recent deployment or config changes.</li>
</ol>
HTML,
);
