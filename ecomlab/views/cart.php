<?php
/**
 * cart.php — the shopping cart (Tasks 11 & 12).
 *
 * Two separate forms over one table, matching the handout: a Remove form
 * (checkboxes) and an Update form (quantity inputs). Each posts to its own
 * Action, because each does one thing.
 *
 * MVC note: display only. Totals come from the Controller; the view runs no
 * SQL and performs no writes.
 */

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$controller = new CartController();
$ip    = get_ip();
$items = $controller->getCartItems($ip);
$total = $controller->getCartTotal($ip);

$page_title = 'Your Cart';
require_once __DIR__ . '/layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <section class="content">
        <h1>Your Cart</h1>

        <?php require __DIR__ . '/layout/flash.php'; ?>

        <?php if (!$items): ?>
            <p class="muted">Your cart is empty.</p>
            <p><a class="btn" href="<?php echo app_url('index.php'); ?>">Continue Shopping</a></p>
        <?php else: ?>

        <!-- Update quantities. One row per cart line: update[] carries the
             product id, qty[] the new quantity, matched by position. -->
        <form id="cart-update-form" method="POST"
              action="<?php echo app_url('actions/update_qty_action.php'); ?>">
            <?php echo csrf_field(); ?>
            <table class="table cart-table">
                <thead>
                    <tr>
                        <th>Remove</th>
                        <th>Product</th>
                        <th>Unit price</th>
                        <th>Qty</th>
                        <th>Line total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <?php /* Belongs to the Remove form below — linked by its form attribute. */ ?>
                            <input type="checkbox" form="cart-remove-form"
                                   name="remove[]" value="<?php echo (int) $item['p_id']; ?>"
                                   aria-label="Remove <?php echo htmlspecialchars($item['product_title']); ?>">
                        </td>
                        <td class="cart-product">
                            <img src="<?php echo product_image_url($item['product_image']); ?>"
                                 alt="" width="56" class="thumb-sm">
                            <a href="<?php echo app_url('views/single_product.php?pro_id=' . (int) $item['p_id']); ?>">
                                <?php echo htmlspecialchars($item['product_title']); ?>
                            </a>
                        </td>
                        <td><?php echo money($item['product_price']); ?></td>
                        <td>
                            <input type="hidden" name="update[]" value="<?php echo (int) $item['p_id']; ?>">
                            <input type="number" class="qty-input" name="qty[]" min="1" max="999" step="1"
                                   value="<?php echo (int) $item['qty']; ?>"
                                   aria-label="Quantity for <?php echo htmlspecialchars($item['product_title']); ?>">
                        </td>
                        <td><?php echo money($item['qty'] * $item['product_price']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="cart-subtotal-label">Subtotal</th>
                        <th class="cart-subtotal"><?php echo money($total); ?></th>
                    </tr>
                </tfoot>
            </table>

            <button type="submit" class="btn">Update Quantity</button>
        </form>

        <!-- Remove form. The checkboxes above point at it via form="…", so the
             table is not nested inside two forms. -->
        <form id="cart-remove-form" method="POST"
              action="<?php echo app_url('actions/remove_from_cart_action.php'); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn">Remove from Cart</button>
        </form>

        <p class="cart-buttons">
            <a class="btn" href="<?php echo app_url('index.php'); ?>">Continue Shopping</a>
            <a class="btn" href="<?php echo app_url('views/checkout.php'); ?>">Checkout</a>
        </p>

        <?php endif; ?>
    </section>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
