<?php
/**
 * update_category_action.php — rename a category (admin only).
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

$id   = filter_input(INPUT_POST, 'cat_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$name = trim(strip_tags($_POST['cat_name'] ?? ''));

if (!$id) {
    $_SESSION['error'] = 'Invalid category.';
    redirect(app_url('views/admin/category.php'));
}
if ($name === '' || mb_strlen($name) > 100) {
    $_SESSION['error'] = 'Category name is required (max 100 characters).';
    redirect(app_url('views/admin/category.php?edit_id=' . $id));
}

$result = (new ProductController())->updateCategory($id, $name);
$_SESSION[$result['success'] ? 'success' : 'error'] =
    $result['success'] ? 'Category updated.' : $result['error'];

redirect(app_url('views/admin/category.php'));
