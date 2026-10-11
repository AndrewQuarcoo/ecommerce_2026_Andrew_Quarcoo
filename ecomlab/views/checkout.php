<?php
/**
 * checkout.php — order summary before paying (Task 12's Checkout button).
 *
 * Login is required here rather than earlier: a guest may browse and build a
 * cart by IP, but an order has to belong to a customer account.
 *
 * MVC note: display only. Task 13 adds the payment step this page hands off to.
 */

require_once __DIR__ . '/../core/core.php';
require_login();
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

$controller = new CartController();
$ip    = get_ip();
$items = $controller->getCartItems($ip);
$total = $controller->getCartTotal($ip);

if (!$items) {
    $_SESSION['error'] = 'Your cart is empty.';
    redirect(app_url('views/cart.php'));
}

$profile = (new CustomerController())->getProfileByEmail($_SESSION['customer_email']);

$page_title = 'Checkout';
require_once __DIR__ . '/layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <section class="content">
        <h1>Checkout</h1>
        <?php require __DIR__ . '/layout/flash.php'; ?>

        <div class="card wide">
            <h2>Order summary</h2>
            <table class="table">
                <thead><tr><th>Product</th><th>Qty</th><th>Unit price</th><th>Line total</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_title']); ?></td>
                        <td><?php echo (int) $item['qty']; ?></td>
                        <td><?php echo money($item['product_price']); ?></td>
                        <td><?php echo money($item['qty'] * $item['product_price']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><th colspan="3">Total</th><th><?php echo money($total); ?></th></tr>
                </tfoot>
            </table>
        </div>

        <div class="card wide">
            <h2>Delivering to</h2>
            <?php if ($profile): ?>
                <p>
                    <?php echo htmlspecialchars($profile['customer_name']); ?><br>
                    <?php if (!empty($profile['customer_address'])): ?>
                        <?php echo nl2br(htmlspecialchars($profile['customer_address'])); ?><br>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($profile['customer_city']); ?>,
                    <?php echo htmlspecialchars($profile['customer_country']); ?><br>
                    <?php echo htmlspecialchars($profile['customer_contact']); ?>
                </p>
            <?php endif; ?>
        </div>

        <p class="cart-buttons">
            <a class="btn" href="<?php echo app_url('views/cart.php'); ?>">Back to Cart</a>
        </p>
    </section>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
