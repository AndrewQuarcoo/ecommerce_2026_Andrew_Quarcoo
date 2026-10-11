<?php
/**
 * single_product.php — one product's detail page (Task 10).
 *
 * Requires a valid ?pro_id=N. Anything else (missing, non-numeric, or an id
 * with no matching row) sends the visitor home rather than rendering an
 * empty page.
 *
 * MVC note: display only — getProductById() comes through the Controller.
 */

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

$proId   = filter_input(INPUT_GET, 'pro_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$product = $proId ? $controller->getProductById($proId) : false;

if (!$product) {
    $_SESSION['error'] = 'That product could not be found.';
    redirect(app_url('index.php'));
}

$page_title = $product['product_title'];
require_once __DIR__ . '/layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <section class="content">
        <?php require __DIR__ . '/layout/flash.php'; ?>

        <p class="sub"><a href="<?php echo app_url('index.php'); ?>">&larr; Back to products</a></p>

        <div class="product-detail">
            <div class="product-detail-image">
                <img src="<?php echo product_image_url($product['product_image']); ?>"
                     alt="<?php echo htmlspecialchars($product['product_title']); ?>" width="360">
            </div>

            <div class="product-detail-body">
                <h1><?php echo htmlspecialchars($product['product_title']); ?></h1>

                <p class="product-meta">
                    Category:
                    <a href="<?php echo app_url('index.php?cat=' . (int) $product['product_cat']); ?>">
                        <?php echo htmlspecialchars($product['cat_name']); ?></a>
                    &middot;
                    Brand:
                    <a href="<?php echo app_url('index.php?brand=' . (int) $product['product_brand']); ?>">
                        <?php echo htmlspecialchars($product['brand_name']); ?></a>
                </p>

                <p class="product-price-lg"><?php echo money($product['product_price']); ?></p>

                <?php if (!empty($product['product_desc'])): ?>
                    <h2>Description</h2>
                    <p><?php echo nl2br(htmlspecialchars($product['product_desc'])); ?></p>
                <?php endif; ?>

                <?php if (!empty($product['product_keywords'])): ?>
                    <p class="product-keywords">
                        Keywords: <?php echo htmlspecialchars($product['product_keywords']); ?>
                    </p>
                <?php endif; ?>

                <p class="product-actions">
                    <a class="btn" href="<?php echo app_url('actions/add_to_cart_action.php?add_cart=' . (int) $product['product_id']); ?>">Add to Cart</a>
                </p>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
