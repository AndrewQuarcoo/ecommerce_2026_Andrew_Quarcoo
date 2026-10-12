<?php
/**
 * orders.php — the customer's order history.
 *
 * Without this page a receipt is visible exactly once, on the redirect that
 * follows payment. Close the tab and it is gone: nothing else links to an
 * invoice, and guessing ?order_id values is refused because invoices are
 * scoped to their owner. This gives every past order a way back.
 *
 * MVC note: display only. The list comes from the Controller, and the
 * WHERE customer_id = ? in the Model is what keeps one customer's orders
 * out of another's history.
 */

require_once __DIR__ . '/../../core/core.php';
require_login();
require_once __DIR__ . '/../../controllers/CartController.php';

$orders = (new CartController())->getOrdersByCustomer($_SESSION['customer_id']);

$page_title = 'My Orders';
require_once __DIR__ . '/../layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/../layout/sidebar.php'; ?>

    <section class="content">
        <h1>My Orders</h1>
        <?php require __DIR__ . '/../layout/flash.php'; ?>

        <?php if (!$orders): ?>
            <p class="muted">You have not placed any orders yet.</p>
            <p><a class="btn" href="<?php echo app_url('index.php'); ?>">Start shopping</a></p>
        <?php else: ?>
            <p class="sub">
                <?php echo count($orders); ?>
                order<?php echo count($orders) === 1 ? '' : 's'; ?>.
                Open any one to see or print its receipt.
            </p>

            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice</th><th>Date</th><th>Items</th>
                        <th>Total</th><th>Status</th><th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td>#<?php echo (int) $o['invoice_no']; ?></td>
                        <td><?php echo htmlspecialchars($o['order_date']); ?></td>
                        <td><?php echo (int) $o['item_count']; ?></td>
                        <td><?php echo money($o['total']); ?></td>
                        <td><?php echo htmlspecialchars($o['order_status']); ?></td>
                        <td>
                            <a class="btn-sm"
                               href="<?php echo app_url('views/payment_success.php?order_id=' . (int) $o['order_id']); ?>">
                                View receipt
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p class="form-foot">
            <a href="<?php echo app_url('views/account/my_account.php'); ?>">&larr; Back to My Account</a>
        </p>
    </section>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
