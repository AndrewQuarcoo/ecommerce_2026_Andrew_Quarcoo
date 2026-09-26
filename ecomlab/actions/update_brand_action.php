<?php
/**
 * update_brand_action.php — rename a brand (admin only).
 */
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_url('views/admin/brand.php'));
}

$id   = filter_input(INPUT_POST, 'brand_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$name = trim(strip_tags($_POST['brand_name'] ?? ''));

if (!$id) {
    $_SESSION['error'] = 'Invalid brand.';
    redirect(app_url('views/admin/brand.php'));
}
if ($name === '' || mb_strlen($name) > 100) {
    $_SESSION['error'] = 'Brand name is required (max 100 characters).';
    redirect(app_url('views/admin/brand.php?edit_id=' . $id));
}

$result = (new ProductController())->updateBrand($id, $name);
$_SESSION[$result['success'] ? 'success' : 'error'] =
    $result['success'] ? 'Brand updated.' : $result['error'];

redirect(app_url('views/admin/brand.php'));
