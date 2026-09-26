<?php
/**
 * my_account.php — the logged-in customer's account page (View).
 * Protected: require_login() runs before any output.
 */
require_once __DIR__ . '/../../core/core.php';
require_login();

require_once __DIR__ . '/../../controllers/CustomerController.php';
$controller = new CustomerController();
$profile = $controller->getProfileByEmail($_SESSION['customer_email']);

$page_title = 'My Account';
require_once __DIR__ . '/../layout/header.php';
?>
<div class="card wide">
    <h1>My Account</h1>
    <p class="sub">Your shoppn profile.</p>

    <?php require __DIR__ . '/../layout/flash.php'; ?>

    <?php if ($profile): ?>
        <dl class="account-grid">
            <dt>Name</dt>    <dd><?php echo htmlspecialchars($profile['customer_name']); ?></dd>
            <dt>Email</dt>   <dd><?php echo htmlspecialchars($profile['customer_email']); ?></dd>
            <dt>Country</dt> <dd><?php echo htmlspecialchars($profile['customer_country']); ?></dd>
            <dt>City</dt>    <dd><?php echo htmlspecialchars($profile['customer_city']); ?></dd>
            <dt>Contact</dt> <dd><?php echo htmlspecialchars($profile['customer_contact']); ?></dd>
            <?php if (!empty($profile['customer_address'])): ?>
                <dt>Address</dt> <dd><?php echo nl2br(htmlspecialchars($profile['customer_address'])); ?></dd>
            <?php endif; ?>
            <dt>Role</dt>    <dd><?php echo ((int) $profile['user_role'] === 1) ? 'Administrator' : 'Customer'; ?></dd>
            <dt>Joined</dt>  <dd><?php echo htmlspecialchars($profile['created_at'] ?? '—'); ?></dd>
        </dl>
    <?php else: ?>
        <p class="muted">Could not load your profile.</p>
    <?php endif; ?>

    <p class="form-foot">
        <a class="btn-sm" href="<?php echo app_url('logout.php'); ?>">Logout</a>
    </p>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
