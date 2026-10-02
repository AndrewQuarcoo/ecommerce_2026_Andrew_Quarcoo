<?php
/**
 * add_brand_action.php — Task 5: create a brand (admin only).
 *
 * MVC note: an Action is the server entry point. It guards access, reads and
 * validates input, calls the Controller, flashes a message and redirects.
 * No SQL, no HTML output.
 */

require_once __DIR__ . '/../core/core.php';
require_admin();   // before any output — a customer or guest never gets here
require_once __DIR__ . '/../controllers/ProductController.php';

$back = app_url('views/admin/brand.php');

// Only ever run on a POST submission.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($back);
}

// Reject forged cross-site submissions.
if (!verify_csrf()) {
    redirect($back);
}

$name = trim(strip_tags($_POST['brand_name'] ?? ''));

// brands.brand_name is VARCHAR(100) — reject anything longer.
if ($name === '' || mb_strlen($name) > 100) {
    $_SESSION['error'] = 'Brand name is required (max 100 characters).';
    redirect($back);
}

$result = (new ProductController())->addBrand($name);

if (!empty($result['success'])) {
    $_SESSION['success'] = 'Brand added.';
} else {
    $_SESSION['error'] = $result['error'];
}

redirect($back);
