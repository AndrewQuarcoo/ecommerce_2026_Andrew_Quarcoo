<?php
/**
 * home.php — the store landing page and product grid (Task 10).
 *
 * One page, three listings, chosen by the query string:
 *   ?cat=N    → every product in that category
 *   ?brand=N  → every product of that brand
 *   (neither) → a random featured selection
 *
 * MVC note: the View asks the Controller for rows and loops them. It runs no
 * SQL. Loaded via index.php, which has already required core/core.php.
 */
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/../core/core.php';
}
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

// Ids arriving from the URL are validated before they reach the Model.
$catId   = filter_input(INPUT_GET, 'cat',   FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$brandId = filter_input(INPUT_GET, 'brand', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($catId) {
    $category   = $controller->getCategoryById($catId);
    $products   = $category ? $controller->getProductsByCategory($catId) : [];
    $heading    = $category ? htmlspecialchars($category['cat_name']) : 'Category not found';
    $page_title = $category ? $category['cat_name'] : 'Category';
} elseif ($brandId) {
    $brand      = $controller->getBrandById($brandId);
    $products   = $brand ? $controller->getProductsByBrand($brandId) : [];
    $heading    = $brand ? htmlspecialchars($brand['brand_name']) : 'Brand not found';
    $page_title = $brand ? $brand['brand_name'] : 'Brand';
} else {
    $products   = $controller->getFeaturedProducts(6);
    $heading    = null;
    $page_title = 'Home';
}

require_once __DIR__ . '/layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <section class="content">
        <?php require __DIR__ . '/layout/flash.php'; ?>

        <?php if ($heading === null): ?>
            <div class="hero">
                <h1>Welcome to shoppn</h1>
                <?php if (is_logged_in()): ?>
                    <p>Good to see you, <strong><?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'friend'); ?></strong>.</p>
                <?php else: ?>
                    <p>Your store for everything. <a href="<?php echo app_url('views/register.php'); ?>">Create an account</a>
                       or <a href="<?php echo app_url('views/login.php'); ?>">log in</a> to get started.</p>
                <?php endif; ?>
            </div>
            <h2>Featured products</h2>
        <?php else: ?>
            <h1><?php echo $heading; ?></h1>
            <p class="sub"><a href="<?php echo app_url('index.php'); ?>">&larr; All products</a></p>
        <?php endif; ?>

        <?php if ($products): ?>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <?php require __DIR__ . '/layout/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="muted">No products found.</p>
        <?php endif; ?>
    </section>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
