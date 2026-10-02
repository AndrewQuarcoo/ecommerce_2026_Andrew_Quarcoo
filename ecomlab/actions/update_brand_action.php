<?php
/**
 * update_brand_action.php — Task 6: rename a brand (admin only).
 *
 * MVC note: the UPDATE itself lives in the Model. This file only guards,
 * validates, delegates and redirects.
 */

require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

$back = app_url('views/admin/brand.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($back);
}
if (!verify_csrf()) {
    redirect($back);
}

// brand_id must be a positive integer — anything else is a tampered form.
$id   = filter_input(INPUT_POST, 'brand_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$name = trim(strip_tags($_POST['brand_name'] ?? ''));

if (!$id) {
    $_SESSION['error'] = 'Invalid brand.';
    redirect($back);
}
if ($name === '' || mb_strlen($name) > 100) {
    $_SESSION['error'] = 'Brand name is required (max 100 characters).';
    redirect($back . '?edit_id=' . $id);   // keep the user in edit mode
}

$result = (new ProductController())->updateBrand($id, $name);

if (!empty($result['success'])) {
    $_SESSION['success'] = 'Brand updated.';
    redirect($back);
}

$_SESSION['error'] = $result['error'];
redirect($back . '?edit_id=' . $id);
