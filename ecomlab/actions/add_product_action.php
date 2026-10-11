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
$image  = null;
if (empty($_FILES['product_image']['name'])) {
    $errors[] = 'A product image is required.';
} else {
    $upload = save_uploaded_image($_FILES['product_image'],
                                  __DIR__ . '/../images/products', 'prod_');
    if (!$upload['ok']) {
        $errors[] = $upload['error'];
    } else {
        $image = $upload['filename'];
    }
}

if ($errors) {
    $_SESSION['error'] = implode(' ', $errors);
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
