<?php
/**
 * add_product_action.php — Task 9: create a product (admin only).
 *
 * MVC note: this Action validates input, moves the uploaded file, then hands
 * plain values to the Controller. The Model only ever stores the filename —
 * it knows nothing about $_FILES.
 */

require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/product_input.php';

$back = app_url('views/admin/product.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($back);
}
if (!verify_csrf()) {
    redirect($back);
}

$in     = read_product_input();
$errors = $in['errors'];

// The image is required when creating a product (there is nothing to fall
// back on), but optional when editing — see update_product_action.php.
// Checked now, stored only after every field has passed, so a submission
// rejected for a bad price does not leave a stray file in images/products.
$upload = validate_uploaded_image($_FILES['product_image'] ?? null);
if (!$upload['present']) {
    $errors[] = 'A product image is required.';
} elseif (!$upload['ok']) {
    $errors[] = $upload['error'];
}

if ($errors) {
    $_SESSION['error'] = implode(' ', $errors);
    $_SESSION['old']   = $in['old'];
    redirect($back);
}

$image = store_uploaded_image($_FILES['product_image'],
                              __DIR__ . '/../images/products', 'prod_', $upload['mime']);
if ($image === null) {
    $_SESSION['error'] = 'Could not save the product image.';
    $_SESSION['old']   = $in['old'];
    redirect($back);
}

$result = (new ProductController())->addProduct(
    $in['cat'], $in['brand'], $in['title'], $in['price'],
    $in['desc'], $image, $in['keywords']
);

if (!empty($result['success'])) {
    unset($_SESSION['old']);
    $_SESSION['success'] = 'Product added.';
    redirect($back);
}

$_SESSION['error'] = $result['error'];
$_SESSION['old']   = $in['old'];
redirect($back);
