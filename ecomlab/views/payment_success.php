<?php
/**
 * payment_success.php — the invoice for a completed order (Task 13).
 *
 * Reads ?order_id=N. Crucially it also checks the order belongs to the
 * logged-in customer: without that, anyone could walk the ids and read other
 * people's invoices, which is an authorisation bug, not just a tidiness one.
 *
 * MVC note: display only.
 */

require_once __DIR__ . '/../core/core.php';
require_login();
require_once __DIR__ . '/../controllers/CartController.php';

$controller = new CartController();

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$order   = $orderId ? $controller->getOrderById($orderId) : false;

// Row-level authorisation: your own orders only.
if (!$order || (int) $order['customer_id'] !== (int) $_SESSION['customer_id']) {
    $_SESSION['error'] = 'That order could not be found.';
    redirect(app_url('index.php'));
}

$items = $controller->getOrderItems($orderId);
$total = 0;
foreach ($items as $i) {
    $total += $i['qty'] * $i['product_price'];
}

$page_title = 'Order #' . (int) $order['invoice_no'];
require_once __DIR__ . '/layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <section class="content">
        <?php require __DIR__ . '/layout/flash.php'; ?>

        <div class="card wide invoice">
            <h1>Thank you — your order is confirmed</h1>

            <dl class="account-grid">
                <dt>Invoice number</dt> <dd>#<?php echo (int) $order['invoice_no']; ?></dd>
                <dt>Order date</dt>     <dd><?php echo htmlspecialchars($order['order_date']); ?></dd>
                <dt>Status</dt>         <dd><?php echo htmlspecialchars($order['order_status']); ?></dd>
                <dt>Customer</dt>       <dd><?php echo htmlspecialchars($order['customer_name']); ?></dd>
                <dt>Email</dt>          <dd><?php echo htmlspecialchars($order['customer_email']); ?></dd>
                <?php if (!empty($order['reference'])): ?>
                    <dt>Payment reference</dt> <dd><?php echo htmlspecialchars($order['reference']); ?></dd>
                <?php endif; ?>
            </dl>

            <h2>Items</h2>
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
                    <tr><th colspan="3">Total paid</th><th><?php echo money($total); ?></th></tr>
                </tfoot>
            </table>

            <p class="form-foot">
                <a class="btn" href="<?php echo app_url('index.php'); ?>">Continue Shopping</a>
            </p>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
