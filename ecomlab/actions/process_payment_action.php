<?php
/**
 * process_payment_action.php — Task 13: verify a Paystack payment and record
 * the order.
 *
 * This file is the whole security story of the payment flow. Paystack's inline
 * JS calls onSuccess in the BROWSER, and anyone can simply type this URL with
 * a made-up reference. So the browser is never believed: the reference is
 * re-checked against Paystack's own API, server-to-server, using the secret
 * key, and the order is only written if Paystack itself says the transaction
 * succeeded, for the right amount, in the right currency.
 *
 * MVC note: reads input, calls the Controller, redirects. No SQL, no HTML.
 */

require_once __DIR__ . '/../core/core.php';
require_login();
require_once __DIR__ . '/../controllers/CartController.php';

$failed = app_url('views/payment_failed.php');

if (!defined('PAYSTACK_SECRET_KEY') || strpos(PAYSTACK_SECRET_KEY, 'xxxx') !== false) {
    log_error('Paystack keys are not configured (core/paystack_cred.php).');
    $_SESSION['error'] = 'Payments are not configured on this server yet.';
    redirect($failed);
}

// Paystack references are their own opaque strings; allow only the safe
// characters they use so nothing odd reaches the API URL.
$reference = trim($_GET['ref'] ?? $_GET['reference'] ?? '');
if ($reference === '' || !preg_match('/^[A-Za-z0-9._-]{6,100}$/', $reference)) {
    $_SESSION['error'] = 'Missing or malformed payment reference.';
    redirect($failed);
}

$controller = new CartController();
$ip         = get_ip();
$customerId = (int) $_SESSION['customer_id'];

// Replay guard: refreshing this URL must not create a second order for one
// payment. The reference column is the natural idempotency key.
$existing = $controller->getPaymentByReference($reference);
if ($existing) {
    redirect(app_url('views/payment_success.php?order_id=' . (int) $existing['order_id']));
}

// What we expect to have been charged, computed from OUR cart, never from
// anything the browser sent.
$expected = round($controller->getCartTotal($ip), 2);
if ($expected <= 0) {
    $_SESSION['error'] = 'Your cart is empty.';
    redirect(app_url('views/cart.php'));
}

// ── Server-to-server verification ──
$ch = curl_init('https://api.paystack.co/transaction/verify/' . rawurlencode($reference));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
                               'Cache-Control: no-cache'],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => true,   // never disable: that is what makes this trustworthy
    CURLOPT_SSL_VERIFYHOST => 2,
]);
$body   = curl_exec($ch);
$curlNo = curl_errno($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlNo !== 0 || $status !== 200 || !$body) {
    log_error("Paystack verify failed (curl=$curlNo http=$status) ref=$reference");
    $_SESSION['error'] = 'We could not confirm your payment with Paystack. You have not been charged twice — please contact us before retrying.';
    redirect($failed);
}

$data = json_decode($body, true);
if (!is_array($data) || empty($data['status']) || ($data['data']['status'] ?? '') !== 'success') {
    log_error('Paystack reported an unsuccessful transaction, ref=' . $reference);
    $_SESSION['error'] = 'Your payment was not successful.';
    redirect($failed);
}

// Amounts come back in the minor unit (pesewas), so compare in that unit and
// avoid float equality entirely.
$paidMinor     = (int) ($data['data']['amount'] ?? 0);
$expectedMinor = (int) round($expected * 100);
$currency      = strtoupper($data['data']['currency'] ?? '');

if ($currency !== PAYSTACK_CURRENCY) {
    log_error("Currency mismatch: paid $currency, expected " . PAYSTACK_CURRENCY . ", ref=$reference");
    $_SESSION['error'] = 'That payment was made in the wrong currency.';
    redirect($failed);
}
if ($paidMinor < $expectedMinor) {
    log_error("Underpayment: paid $paidMinor, expected $expectedMinor, ref=$reference");
    $_SESSION['error'] = 'The amount paid does not match your cart total.';
    redirect($failed);
}

// ── Verified. Record the order. ──
$orderId = $controller->processPayment($customerId, $ip, $reference, $expected);

if (!$orderId) {
    log_error('processPayment returned false after a verified payment, ref=' . $reference);
    $_SESSION['error'] = 'Your payment went through but we could not save the order. Please contact us with reference ' . $reference . '.';
    redirect($failed);
}

$_SESSION['success'] = 'Payment successful. Thank you!';
redirect(app_url('views/payment_success.php?order_id=' . $orderId));
