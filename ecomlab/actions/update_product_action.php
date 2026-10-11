<?php
/**
 * update_product_action.php — Task 9: edit a product (admin only).
 *
 * The only real difference from adding: the image is optional. If no new file
 * is sent, the existing filename travels in a hidden field and is written back
 * unchanged, so editing a title does not wipe the picture.
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

$id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    $_SESSION['error'] = 'Invalid product.';
    redirect($back);
}

$in     = read_product_input();
$errors = $in['errors'];

// Keep the current image unless a new one was uploaded. The hidden field is
// only a filename, so it is stripped of any path before being trusted.
$image = basename(trim($_POST['current_image'] ?? ''));
if ($image === '' || $image === '.' || $image === '..') {
    $image = null;
}
if (!empty($_FILES['product_image']['name'])) {
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
    redirect($back . '?edit_id=' . $id);
}

$result = (new ProductController())->updateProduct(
    $id, $in['cat'], $in['brand'], $in['title'], $in['price'],
    $in['desc'], $image, $in['keywords']
);

if (!empty($result['success'])) {
    $_SESSION['success'] = 'Product updated.';
    redirect($back);
}

$_SESSION['error'] = $result['error'];
redirect($back . '?edit_id=' . $id);
