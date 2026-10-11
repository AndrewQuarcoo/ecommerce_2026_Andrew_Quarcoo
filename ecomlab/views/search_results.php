<?php
/**
 * search_results.php — results for the header search box (Task 10).
 *
 * Reads ?user_query, asks the Controller to search titles and keywords, and
 * renders the hits with the same card partial the home grid uses.
 *
 * MVC note: display only. The LIKE query and its wildcard escaping live in
 * the Model.
 */

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

$query    = trim(strip_tags($_GET['user_query'] ?? ''));
$searched = $query !== '';
$products = $searched ? $controller->searchProducts($query) : [];

$page_title = $searched ? 'Search: ' . $query : 'Search';
require_once __DIR__ . '/layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <section class="content">
        <?php require __DIR__ . '/layout/flash.php'; ?>

        <h1>Search results</h1>

        <?php if (!$searched): ?>
            <p class="muted">Type something into the search box above to find products.</p>
        <?php else: ?>
            <p class="sub">
                <?php echo count($products); ?>
                result<?php echo count($products) === 1 ? '' : 's'; ?>
                for &ldquo;<?php echo htmlspecialchars($query); ?>&rdquo;.
                <a href="<?php echo app_url('index.php'); ?>">All products</a>
            </p>

            <?php if ($products): ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <?php require __DIR__ . '/layout/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted">No products found. Try a different word.</p>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
