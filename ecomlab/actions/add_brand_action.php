<?php
/**
 * add_brand_action.php — create a brand (admin only).
 * MVC: require_admin → sanitise → controller → redirect. No SQL, no HTML.
 */
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_url('views/admin/brand.php'));
}
if (!verify_csrf()) {
    redirect(app_url('views/admin/brand.php'));
}

$name = trim(strip_tags($_POST['brand_name'] ?? ''));
if ($name === '' || mb_strlen($name) > 100) {
    $_SESSION['error'] = 'Brand name is required (max 100 characters).';
    redirect(app_url('views/admin/brand.php'));
}

$result = (new ProductController())->addBrand($name);
$_SESSION[$result['success'] ? 'success' : 'error'] =
    $result['success'] ? 'Brand added.' : $result['error'];

redirect(app_url('views/admin/brand.php'));
