<?php
/**
 * add_to_cart_action.php — Task 11: put a product in the cart.
 *
 * Triggered by a GET link (?add_cart=N), which is the pattern the handout
 * specifies. Note the trade-off: a GET that changes state cannot carry a
 * CSRF token the way a POST form does. It is acceptable here because the
 * worst a forged link can do is add a product to the visitor's own cart —
 * nothing is bought, nothing is deleted, and a repeat is refused as a
 * duplicate. Removing and updating, which are destructive, are POST-only.
 *
 * MVC note: reads GET, calls the Controller, redirects. No SQL, no HTML.
 */

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$productId = filter_input(INPUT_GET, 'add_cart', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

// Return the visitor to the page they clicked from, when that page is ours.
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$back    = app_url('index.php');
if ($referer !== '') {
    $path = parse_url($referer, PHP_URL_PATH);
    $qs   = parse_url($referer, PHP_URL_QUERY);
    $host = parse_url($referer, PHP_URL_HOST);
    // Same-site only: an off-site Referer must never become a redirect target.
    if ($path && (!$host || $host === ($_SERVER['HTTP_HOST'] ?? ''))) {
        $candidate = $path . ($qs ? '?' . $qs : '');
        if (is_safe_local_url($candidate)) {
            $back = $candidate;
        }
    }
}

if (!$productId) {
    $_SESSION['error'] = 'That product could not be found.';
    redirect($back);
}

$result = (new CartController())->addToCart(
    $productId,
    1,                       // new lines always start at 1; the cart page edits it
    get_ip(),
    $_SESSION['customer_id'] ?? null
);

if (!empty($result['success'])) {
    $_SESSION['success'] = 'Added to your cart.';
} else {
    $_SESSION['error'] = $result['error'];
}

redirect($back);
