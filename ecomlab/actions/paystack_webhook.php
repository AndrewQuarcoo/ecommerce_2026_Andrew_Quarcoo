<?php
/**
 * paystack_webhook.php — Paystack tells us a charge succeeded.
 *
 * This solves the case the browser callback cannot: the customer pays, then
 * closes the tab before being redirected back. The money has moved and the
 * site would never hear about it, so the order is never recorded — and worse,
 * their cart is still full, so they may well pay a second time.
 *
 * Unlike every other endpoint here, this one has no session, no login and no
 * CSRF token, because the caller is Paystack's server, not a browser. What
 * takes their place is the signature: Paystack signs the raw request body
 * with HMAC-SHA512 using the secret key that only they and this server hold.
 * Without that check this would be an endpoint anyone could POST a fake
 * "payment succeeded" to.
 *
 * Set the URL in the Paystack dashboard under Settings -> API Keys & Webhooks.
 *
 * MVC note: an Action. Reads input, calls the Controller, returns a status
 * code. No SQL, no HTML.
 */

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/paystack.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

// Webhooks are POSTs and nothing else.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$raw = file_get_contents('php://input');
$sig = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

if (!paystack_signature_valid($raw, $sig)) {
    log_error('Webhook rejected: bad or missing signature from ' . get_ip());
    http_response_code(401);
    exit;
}

$event = json_decode($raw, true);
if (!is_array($event)) {
    http_response_code(400);
    exit;
}

// Anything other than a successful charge is acknowledged and ignored, so
// Paystack does not keep retrying events we have no use for.
if (($event['event'] ?? '') !== 'charge.success') {
    http_response_code(200);
    echo 'ignored';
    exit;
}

$reference = (string) ($event['data']['reference'] ?? '');
if ($reference === '') {
    http_response_code(400);
    exit;
}

$controller = new CartController();

// Idempotency: Paystack retries a webhook until it gets a 200, and the
// browser callback may well have recorded this already.
if ($controller->getPaymentByReference($reference)) {
    http_response_code(200);
    echo 'already recorded';
    exit;
}

// The signature proves the body came from Paystack, but re-verifying through
// the API is cheap and means the order is written from the authoritative
// record rather than from a payload we were handed.
$tx = paystack_verify($reference);
if (!$tx['paid']) {
    log_error('Webhook: charge.success for a reference Paystack does not confirm, ref=' . $reference);
    http_response_code(200);           // acknowledged; retrying will not help
    echo 'not verified';
    exit;
}
if ($tx['currency'] !== PAYSTACK_CURRENCY) {
    log_error('Webhook: wrong currency ' . $tx['currency'] . ' ref=' . $reference);
    http_response_code(200);
    echo 'wrong currency';
    exit;
}

// Which customer, and which cart? The webhook has no session, so both travel
// in the metadata set when the transaction was started. Metadata originates
// in the browser, so it is treated as a claim to be checked, not a fact: the
// customer id is only accepted if the email Paystack recorded for the
// transaction really does belong to that customer.
$customerId = (int) ($tx['metadata']['customer_id'] ?? 0);
$cartIp     = (string) ($tx['metadata']['ip_add'] ?? '');

if ($customerId < 1 || $cartIp === '') {
    log_error('Webhook: transaction has no usable metadata, ref=' . $reference);
    http_response_code(200);
    echo 'no metadata';
    exit;
}

$owner = (new CustomerController())->getCustomerById($customerId);
if (!$owner || strtolower($owner['customer_email']) !== $tx['email']) {
    log_error('Webhook: metadata customer_id does not match the paying email, ref=' . $reference);
    http_response_code(200);
    echo 'metadata mismatch';
    exit;
}

// Record it from the cart the payment was made against.
$amount  = round($tx['amount'] / 100, 2);
$orderId = $controller->processPayment($customerId, $cartIp, $reference, $amount);

if (!$orderId) {
    // 500 asks Paystack to retry — the cart may simply not have been readable
    // for a moment, and a retry is exactly what should happen.
    log_error('Webhook: could not record a verified payment, ref=' . $reference);
    http_response_code(500);
    echo 'retry';
    exit;
}

log_error('Webhook recorded order ' . $orderId . ' from ref=' . $reference);
http_response_code(200);
echo 'ok';
