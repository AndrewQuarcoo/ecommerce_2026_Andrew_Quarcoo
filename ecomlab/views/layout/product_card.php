<?php
/**
 * product_card.php — one product tile.
 *
 * Included inside a foreach by home.php and search_results.php so both pages
 * render results identically. Expects $product (one joined product row).
 *
 * MVC note: display only — no SQL, no business logic.
 */
?>
<article class="product-card">
    <a class="product-thumb" href="<?php echo app_url('views/single_product.php?pro_id=' . (int) $product['product_id']); ?>">
        <img src="<?php echo product_image_url($product['product_image']); ?>"
             alt="<?php echo htmlspecialchars($product['product_title']); ?>"
             width="220" loading="lazy">
    </a>

    <h3 class="product-title">
        <a href="<?php echo app_url('views/single_product.php?pro_id=' . (int) $product['product_id']); ?>">
            <?php echo htmlspecialchars($product['product_title']); ?>
        </a>
    </h3>

    <p class="product-meta">
        <?php echo htmlspecialchars($product['cat_name']); ?>
        &middot;
        <?php echo htmlspecialchars($product['brand_name']); ?>
    </p>

    <p class="product-price"><?php echo money($product['product_price']); ?></p>

    <p class="product-actions">
        <a class="btn-sm" href="<?php echo app_url('views/single_product.php?pro_id=' . (int) $product['product_id']); ?>">Details</a>
        <a class="btn-sm" href="<?php echo app_url('actions/add_to_cart_action.php?add_cart=' . (int) $product['product_id']); ?>">Add to Cart</a>
    </p>
</article>
