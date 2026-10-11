<?php
/**
 * update_qty_action.php — Task 12: change cart quantities.
 *
 * POST only. The cart page sends two parallel arrays: update[] holds the
 * product id of each row, qty[] the quantity typed for it. They are walked
 * together by index, and each quantity is validated before it reaches the
 * Model — a blank, zero, negative or non-numeric value is refused rather
 * than silently written.
 *
 * MVC note: validates input, calls the Controller, redirects. No SQL.
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

$ids  = $_POST['update'] ?? [];
$qtys = $_POST['qty'] ?? [];

if (!is_array($ids) || !is_array($qtys) || !$ids) {
    $_SESSION['error'] = 'Nothing to update.';
    redirect($back);
}

$controller = new CartController();
$ip      = get_ip();
$updated = 0;
$errors  = [];

foreach ($ids as $i => $rawId) {
    $id  = filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $qty = filter_var($qtys[$i] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (!$id) {
        continue;                       // tampered row id — ignore it
    }
    if (!$qty) {
        $errors[] = 'Quantity must be a whole number of 1 or more.';
        continue;
    }
    if ($qty > 999) {
        $errors[] = 'Quantity cannot be more than 999.';
        continue;
    }
    if (!empty($controller->updateQty($id, $qty, $ip)['success'])) {
        $updated++;
    }
}

if ($updated > 0) {
    $_SESSION['success'] = 'Cart updated.';
}
if ($errors) {
    $_SESSION['error'] = implode(' ', array_unique($errors));
}

redirect($back);
