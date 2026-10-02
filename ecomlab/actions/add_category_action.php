<?php
/**
 * add_category_action.php — Task 7: create a category (admin only).
 *
 * Deliberately the same shape as add_brand_action.php — Task 7 repeats the
 * Task 5 MVC flow so the pattern sticks.
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

$name = trim(strip_tags($_POST['cat_name'] ?? ''));

// categories.cat_name is VARCHAR(100) — reject anything longer.
if ($name === '' || mb_strlen($name) > 100) {
    $_SESSION['error'] = 'Category name is required (max 100 characters).';
    redirect($back);
}

$result = (new ProductController())->addCategory($name);

if (!empty($result['success'])) {
    $_SESSION['success'] = 'Category added.';
} else {
    $_SESSION['error'] = $result['error'];
}

redirect($back);
