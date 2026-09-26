<?php
/**
 * sidebar.php — categories & brands navigation.
 *
 * MVC note: a View asks the Controller for data — it never runs SQL itself.
 * ProductController is introduced on the tasks 5–8 branch; until it exists
 * this sidebar degrades gracefully to empty lists (the tables are empty in
 * the auth-only build anyway).
 */
$categories = [];
$brands     = [];

$productControllerFile = __DIR__ . '/../../controllers/ProductController.php';
if (file_exists($productControllerFile)) {
    require_once $productControllerFile;
    $productController = new ProductController();
    $categories = $productController->getAllCategories();
    $brands     = $productController->getAllBrands();
}
?>
<aside class="sidebar">
    <section class="sidebar-block">
        <h3>Categories</h3>
        <?php if (!empty($categories)): ?>
            <ul>
                <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?php echo app_url('index.php?cat=' . (int) $cat['cat_id']); ?>">
                            <?php echo htmlspecialchars($cat['cat_name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="muted">No categories yet.</p>
        <?php endif; ?>
    </section>

    <section class="sidebar-block">
        <h3>Brands</h3>
        <?php if (!empty($brands)): ?>
            <ul>
                <?php foreach ($brands as $brand): ?>
                    <li>
                        <a href="<?php echo app_url('index.php?brand=' . (int) $brand['brand_id']); ?>">
                            <?php echo htmlspecialchars($brand['brand_name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="muted">No brands yet.</p>
        <?php endif; ?>
    </section>
</aside>
