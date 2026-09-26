<?php
/**
 * login.php — the login form (View).
 */
require_once __DIR__ . '/../core/core.php';

if (is_logged_in()) {
    redirect(app_url('views/account/my_account.php'));
}

$page_title = 'Login';
require_once __DIR__ . '/layout/header.php';
?>
<div class="card">
    <h1>Log in</h1>
    <p class="sub">Welcome back to shoppn.</p>

    <?php require __DIR__ . '/layout/flash.php'; ?>

    <form id="login-form" action="<?php echo app_url('actions/login_action.php'); ?>" method="POST" novalidate>
        <?php echo csrf_field(); ?>
        <div class="form-row">
            <label for="login_email">Email</label>
            <input type="email" id="login_email" name="login_email" maxlength="50">
            <div class="field-error" id="error-login-email"></div>
        </div>

        <div class="form-row">
            <label for="login_pass">Password</label>
            <input type="password" id="login_pass" name="login_pass">
            <div class="field-error" id="error-login-pass"></div>
        </div>

        <button type="submit" class="btn" id="login-submit">Log in</button>
    </form>

    <p class="form-foot">New to shoppn?
        <a href="<?php echo app_url('views/register.php'); ?>">Create an account</a>.</p>
</div>

<script src="<?php echo app_url('js/validate.js'); ?>"></script>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
