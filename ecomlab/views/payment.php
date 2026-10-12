<?php
/**
 * payment.php — pay for the cart with Paystack (Task 13).
 *
 * CSP_PAYSTACK is defined BEFORE core.php is required, because core.php sends
 * the Content-Security-Policy header the moment it loads. This is the only
 * page allowed to load js.paystack.co and frame checkout.paystack.com.
 *
 * MVC note: display only. The amount shown here is recomputed server-side in
 * process_payment_action.php before anything is recorded — nothing this page
 * renders is trusted on the way back.
 */

define('CSP_PAYSTACK', true);

require_once __DIR__ . '/../core/core.php';
require_login();
require_once __DIR__ . '/../controllers/CartController.php';

$controller = new CartController();
$ip    = get_ip();

// Before showing a Pay button, check with Paystack whether this customer has
// already paid for exactly this cart and we simply never heard about it —
// which is what happens if they closed the tab before the callback ran.
// Without this they would be charged a second time for the same goods.
$recovered = $controller->recoverUnrecordedPayment(
    $_SESSION['customer_id'], $_SESSION['customer_email'], $ip
);
if ($recovered) {
    $_SESSION['success'] = 'You had already paid for this order — here is your receipt.';
    redirect(app_url('views/payment_success.php?order_id=' . $recovered));
}

$items = $controller->getCartItems($ip);
$total = round($controller->getCartTotal($ip), 2);

if (!$items) {
    $_SESSION['error'] = 'Your cart is empty.';
    redirect(app_url('views/cart.php'));
}

$configured = defined('PAYSTACK_PUBLIC_KEY') && strpos(PAYSTACK_PUBLIC_KEY, 'xxxx') === false;

$page_title = 'Payment';
require_once __DIR__ . '/layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <section class="content">
        <h1>Payment</h1>
        <?php require __DIR__ . '/layout/flash.php'; ?>

        <div class="card wide">
            <h2>You are paying for</h2>
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
            <?php if (!$configured): ?>
                <p class="flash flash-error">
                    Paystack keys are not set on this server. Copy
                    <code>core/paystack_cred.example.php</code> to
                    <code>core/paystack_cred.php</code> and add your test keys.
                </p>
            <?php else: ?>
                <p>You will be charged <strong><?php echo money($total); ?></strong>.</p>
                <div class="field-error" id="pay-error"></div>
                <?php /* Values travel as data- attributes rather than being echoed
                        into inline JavaScript, so nothing has to be escaped twice. */ ?>
                <button type="button" class="btn" id="pay-button"
                        data-key="<?php echo htmlspecialchars(PAYSTACK_PUBLIC_KEY); ?>"
                        data-email="<?php echo htmlspecialchars($_SESSION['customer_email']); ?>"
                        data-amount="<?php echo (int) round($total * 100); ?>"
                        data-currency="<?php echo htmlspecialchars(PAYSTACK_CURRENCY); ?>"
                        data-callback="<?php echo htmlspecialchars(app_url('actions/process_payment_action.php')); ?>"
                        data-customer="<?php echo (int) $_SESSION['customer_id']; ?>"
                        data-ip="<?php echo htmlspecialchars($ip); ?>">
                    Pay with Paystack
                </button>
                <p class="hint">
                    Test card: 4084 0840 8408 4081, any future expiry, CVV 408.
                </p>
            <?php endif; ?>
            <p class="form-foot"><a href="<?php echo app_url('views/cart.php'); ?>">Back to Cart</a></p>
        </div>
    </section>
</div>

<?php if ($configured): ?>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script src="<?php echo app_url('js/payment.js'); ?>"></script>
<?php endif; ?>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
