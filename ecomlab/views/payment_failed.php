<?php
/**
 * payment_failed.php — shown when a payment could not be confirmed (Task 13).
 *
 * Deliberately says only that it failed. The specific reason (a currency
 * mismatch, an underpayment, a reference Paystack does not recognise) is
 * written to error/error.log for us, not shown to the visitor, because those
 * details tell someone probing the endpoint exactly which check they tripped.
 */

require_once __DIR__ . '/../core/core.php';
require_login();

$page_title = 'Payment failed';
require_once __DIR__ . '/layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <section class="content">
        <div class="card wide">
            <h1>Payment not completed</h1>
            <?php require __DIR__ . '/layout/flash.php'; ?>
            <p>Your order has not been placed and your cart is untouched.</p>
            <p class="form-foot">
                <a class="btn" href="<?php echo app_url('views/payment.php'); ?>">Try again</a>
                <a class="btn" href="<?php echo app_url('views/cart.php'); ?>">Back to Cart</a>
            </p>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
