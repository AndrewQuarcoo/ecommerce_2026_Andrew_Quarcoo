<?php
/**
 * remove_from_cart_action.php — Task 12: remove items from the cart.
 *
 * POST only, because it destroys data. The cart page submits a checkbox
 * group (remove[]), so this handles one or many ids in a single request.
 *
 * MVC note: validates ids, calls the Controller once per id, redirects.
 */

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$back = app_url('views/cart.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($back);
}
if (!verify_csrf()) {
    redirect($back);
}

$ids = $_POST['remove'] ?? [];
if (!is_array($ids) || !$ids) {
    $_SESSION['error'] = 'Tick the items you want to remove.';
    redirect($back);
}

$controller = new CartController();
$ip      = get_ip();
$removed = 0;
$bad     = 0;

foreach ($ids as $raw) {
    $id = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) {
        $bad++;
        continue;
    }
    // The Model scopes the DELETE by IP too, so this can only ever touch
    // the current visitor's own cart.
    if (!empty($controller->removeFromCart($id, $ip)['success'])) {
        $removed++;
    }
}

if ($removed > 0) {
    $_SESSION['success'] = $removed === 1
        ? 'Item removed from your cart.'
        : $removed . ' items removed from your cart.';
}
if ($bad > 0 && $removed === 0) {
    $_SESSION['error'] = 'Those items could not be removed.';
}

redirect($back);
