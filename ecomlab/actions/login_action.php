<?php
/**
 * login_action.php — processes the login form.
 *
 * MVC note: sanitise → call Controller → set session → redirect. No SQL, no HTML.
 */

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_url('views/login.php'));
}

$email = trim(strip_tags($_POST['login_email'] ?? ''));
$pass  = $_POST['login_pass'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') {
    $_SESSION['error'] = 'Please enter a valid email and password.';
    redirect(app_url('views/login.php'));
}

$controller = new CustomerController();
$result = $controller->login($email, $pass);

// A successful login returns the customer row (has customer_id); failure
// returns ['success' => false, 'error' => ...].
if (isset($result['customer_id'])) {
    $_SESSION['customer_id']    = $result['customer_id'];
    $_SESSION['customer_name']  = $result['customer_name'];
    $_SESSION['customer_email'] = $result['customer_email'];
    $_SESSION['user_role']      = $result['user_role'];
    $_SESSION['success']        = 'Welcome back, ' . $result['customer_name'] . '!';

    // Bounce back to the page they were trying to reach, if any.
    $back = $_SESSION['redirect_after_login'] ?? null;
    unset($_SESSION['redirect_after_login']);
    if ($back) {
        redirect($back);
    }
    redirect(app_url('index.php'));
}

$_SESSION['error'] = $result['error'] ?? 'Invalid email or password.';
redirect(app_url('views/login.php'));
