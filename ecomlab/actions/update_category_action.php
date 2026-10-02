<?php
/**
 * update_category_action.php — Task 8: rename a category (admin only).
 *
 * Mirrors update_brand_action.php (Task 6).
 */

require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

$back = app_url('views/admin/category.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($back);
}
if (!verify_csrf()) {
    redirect($back);
}

$id   = filter_input(INPUT_POST, 'cat_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$name = trim(strip_tags($_POST['cat_name'] ?? ''));

if (!$id) {
    $_SESSION['error'] = 'Invalid category.';
    redirect($back);
}
if ($name === '' || mb_strlen($name) > 100) {
    $_SESSION['error'] = 'Category name is required (max 100 characters).';
    redirect($back . '?edit_id=' . $id);
}

$result = (new ProductController())->updateCategory($id, $name);

if (!empty($result['success'])) {
    $_SESSION['success'] = 'Category updated.';
    redirect($back);
}

$_SESSION['error'] = $result['error'];
redirect($back . '?edit_id=' . $id);
