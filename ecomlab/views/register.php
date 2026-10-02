<?php
/**
 * register.php — the registration form (View).
 *
 * Flow: this form → js/validate.js (client validation) →
 * actions/register_action.php → CustomerController → Customer model → DB.
 */
require_once __DIR__ . '/../core/core.php';

// Already logged in? No need to register.
if (is_logged_in()) {
    redirect(app_url('views/account/my_account.php'));
}

$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
function old($key, $old) { return htmlspecialchars($old[$key] ?? ''); }

$countries = ['Ghana', 'Nigeria', 'Kenya', 'South Africa', 'United States',
              'United Kingdom', 'Canada', 'Germany', 'France', 'India', 'Other'];

$page_title = 'Register';
require_once __DIR__ . '/layout/header.php';
?>
<div class="card wide">
    <h1>Create your account</h1>
    <p class="sub">Join shoppn — it only takes a minute.</p>

    <?php require __DIR__ . '/layout/flash.php'; ?>

    <form id="register-form" action="<?php echo app_url('actions/register_action.php'); ?>"
          method="POST" enctype="multipart/form-data" novalidate>
        <?php echo csrf_field(); ?>

        <div class="form-row">
            <label for="customer_name">Full Name</label>
            <input type="text" id="customer_name" name="customer_name"
                   value="<?php echo old('customer_name', $old); ?>" maxlength="100">
            <div class="field-error" id="error-name"></div>
        </div>

        <div class="form-row">
            <label for="customer_email">Email</label>
            <input type="email" id="customer_email" name="customer_email"
                   value="<?php echo old('customer_email', $old); ?>" maxlength="50">
            <div class="field-error" id="error-email"></div>
        </div>

        <div class="form-row">
            <label for="customer_pass">Password</label>
            <input type="password" id="customer_pass" name="customer_pass">
            <p class="hint">At least 8 characters, including an uppercase letter,
               a lowercase letter, a number and a special character.</p>
            <div class="field-error" id="error-pass"></div>
        </div>

        <div class="form-grid">
            <div class="form-row">
                <label for="customer_country">Country</label>
                <select id="customer_country" name="customer_country">
                    <option value="">Select…</option>
                    <?php foreach ($countries as $c): ?>
                        <option value="<?php echo htmlspecialchars($c); ?>"
                            <?php echo (($old['customer_country'] ?? '') === $c) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="field-error" id="error-country"></div>
            </div>

            <div class="form-row">
                <label for="customer_city">City</label>
                <input type="text" id="customer_city" name="customer_city"
                       value="<?php echo old('customer_city', $old); ?>" maxlength="30">
                <div class="field-error" id="error-city"></div>
            </div>
        </div>

        <div class="form-row">
            <label for="customer_contact">Contact Number</label>
            <input type="text" id="customer_contact" name="customer_contact"
                   value="<?php echo old('customer_contact', $old); ?>" maxlength="15"
                   placeholder="e.g. +233 24 000 0000">
            <div class="field-error" id="error-contact"></div>
        </div>

        <div class="form-row">
            <label for="customer_address">Address</label>
            <textarea id="customer_address" name="customer_address" rows="2"
                      maxlength="255"><?php echo old('customer_address', $old); ?></textarea>
            <div class="field-error" id="error-address"></div>
        </div>

        <div class="form-row">
            <label for="customer_image">Profile Image <span class="muted">(optional)</span></label>
            <input type="file" id="customer_image" name="customer_image" accept="image/*">
            <div class="field-error" id="error-image"></div>
        </div>

        <button type="submit" class="btn" id="register-submit">Create Account</button>
    </form>

    <p class="form-foot">Already have an account?
        <a href="<?php echo app_url('views/login.php'); ?>">Log in</a>.</p>
</div>

<script src="<?php echo app_url('js/validate.js'); ?>"></script>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
