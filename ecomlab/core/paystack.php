<?php
/**
 * paystack.php — every call we make to Paystack, in one place.
 *
 * Previously the verify call was written inline in the payment action. It is
 * needed in three places now (the browser callback, the webhook and the
 * recovery sweep), so it lives here rather than being copied three times and
 * drifting apart — the TLS settings in particular must never differ.
 *
 * Nothing here touches the database or prints anything.
 */

require_once __DIR__ . '/core.php';

/** True when real keys have been installed (not the .example placeholders). */
function paystack_configured()
{
    return defined('PAYSTACK_SECRET_KEY')
        && strpos(PAYSTACK_SECRET_KEY, 'xxxx') === false
        && PAYSTACK_SECRET_KEY !== '';
}

/**
 * One authenticated GET against the Paystack API.
 *
 * SSL verification is deliberately left on. Switching it off is the usual
 * "fix" for certificate trouble and it would make every check in this file
 * worthless: anyone able to intercept the connection could simply answer
 * "success" on Paystack's behalf.
 *
 * @return array ['ok' => bool, 'http' => int, 'data' => array|null, 'error' => string|null]
 */
function paystack_get($path)
{
    if (!paystack_configured()) {
        return ['ok' => false, 'http' => 0, 'data' => null, 'error' => 'Paystack is not configured.'];
    }

    $ch = curl_init('https://api.paystack.co' . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
                                   'Cache-Control: no-cache'],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $body   = curl_exec($ch);
    $curlNo = curl_errno($ch);
    $http   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlNo !== 0 || $http !== 200 || !$body) {
        return ['ok' => false, 'http' => $http, 'data' => null,
                'error' => "curl=$curlNo http=$http"];
    }
    $json = json_decode($body, true);
    if (!is_array($json) || empty($json['status'])) {
        return ['ok' => false, 'http' => $http, 'data' => null, 'error' => 'Unexpected response.'];
    }
    return ['ok' => true, 'http' => $http, 'data' => $json['data'] ?? [], 'error' => null];
}

/**
 * Ask Paystack about one transaction.
 *
 * Note it reads data.status, NOT the top-level status. The envelope's status
 * is true for any successful API *call*, including one reporting a
 * transaction that was abandoned. Confusing the two would record an order for
 * a payment nobody made.
 *
 * @return array ['paid' => bool, 'amount' => int (minor unit), 'currency' => string,
 *                'email' => string, 'metadata' => array, 'error' => string|null]
 */
function paystack_verify($reference)
{
    $res = paystack_get('/transaction/verify/' . rawurlencode($reference));
    if (!$res['ok']) {
        return ['paid' => false, 'amount' => 0, 'currency' => '', 'email' => '',
                'metadata' => [], 'error' => $res['error']];
    }
    $d = $res['data'];
    return [
        'paid'     => ($d['status'] ?? '') === 'success',
        'amount'   => (int) ($d['amount'] ?? 0),
        'currency' => strtoupper($d['currency'] ?? ''),
        'email'    => strtolower($d['customer']['email'] ?? ''),
        'metadata' => is_array($d['metadata'] ?? null) ? $d['metadata'] : [],
        'error'    => null,
    ];
}

/**
 * Recent successful transactions for one email address.
 *
 * This is how a payment is recovered when the customer closed the tab before
 * the browser callback ran: Paystack still knows the charge happened, so we
 * ask it rather than waiting to be told.
 *
 * @return array list of ['reference','amount','currency'] (empty on failure)
 */
function paystack_recent_successful($email, $perPage = 20)
{
    $res = paystack_get('/transaction?status=success&perPage=' . (int) $perPage);
    if (!$res['ok'] || !is_array($res['data'])) {
        return [];
    }
    $email = strtolower(trim($email));
    $out   = [];
    foreach ($res['data'] as $t) {
        if (strtolower($t['customer']['email'] ?? '') !== $email) {
            continue;
        }
        $out[] = [
            'reference' => (string) ($t['reference'] ?? ''),
            'amount'    => (int) ($t['amount'] ?? 0),
            'currency'  => strtoupper($t['currency'] ?? ''),
        ];
    }
    return $out;
}

/**
 * Is this webhook body genuinely from Paystack?
 *
 * Paystack signs the raw request body with HMAC-SHA512 using the secret key.
 * Since only Paystack and this server know that key, a matching signature is
 * proof of origin — which matters enormously, because the webhook has no
 * session and no login and would otherwise be an endpoint anyone could POST
 * a fake "payment succeeded" to.
 *
 * hash_equals is used rather than === so the comparison is timing-safe.
 */
function paystack_signature_valid($rawBody, $signature)
{
    if (!paystack_configured() || !is_string($signature) || $signature === '') {
        return false;
    }
    return hash_equals(hash_hmac('sha512', $rawBody, PAYSTACK_SECRET_KEY), $signature);
}
