<?php
/**
 * home.php — the store landing page.
 *
 * In the auth-only build this shows a welcome and the shared layout
 * (header, sidebar, footer). The product grid arrives with Task 10.
 *
 * Loaded via index.php, which has already required core/core.php.
 */
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/../core/core.php';
}
$page_title = 'Home';
require_once __DIR__ . '/layout/header.php';
?>
<div class="layout-with-sidebar">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>

    <section class="content">
        <?php require __DIR__ . '/layout/flash.php'; ?>

        <div class="hero">
            <h1>Welcome to shoppn</h1>
            <?php if (is_logged_in()): ?>
                <p>Good to see you, <strong><?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'friend'); ?></strong>.</p>
            <?php else: ?>
                <p>Your store for everything. <a href="<?php echo app_url('views/register.php'); ?>">Create an account</a>
                   or <a href="<?php echo app_url('views/login.php'); ?>">log in</a> to get started.</p>
            <?php endif; ?>
        </div>

        <div class="notice">
            <p>The product catalogue lands in a later task. Registration, login and the
               admin brand/category tools are live now.</p>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
