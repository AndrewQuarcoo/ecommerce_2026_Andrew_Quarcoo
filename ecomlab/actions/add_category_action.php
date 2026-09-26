<?php
/**
 * add_category_action.php — create a category (admin only).
 */
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_url('views/admin/category.php'));
}
if (!verify_csrf()) {
    redirect(app_url('views/admin/category.php'));
}

$name = trim(strip_tags($_POST['cat_name'] ?? ''));
if ($name === '' || mb_strlen($name) > 100) {
    $_SESSION['error'] = 'Category name is required (max 100 characters).';
    redirect(app_url('views/admin/category.php'));
}

$result = (new ProductController())->addCategory($name);
$_SESSION[$result['success'] ? 'success' : 'error'] =
    $result['success'] ? 'Category added.' : $result['error'];

redirect(app_url('views/admin/category.php'));
