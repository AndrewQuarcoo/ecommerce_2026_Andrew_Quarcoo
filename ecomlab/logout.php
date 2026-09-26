<?php
/**
 * logout.php — securely destroy the session and return home.
 */
require_once __DIR__ . '/core/core.php';

// Clear all session data.
$_SESSION = [];

// Delete the session cookie in the browser.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}

// Destroy the session on the server, then start a fresh one for the flash.
session_destroy();
session_start();
$_SESSION['success'] = 'You have been logged out.';

redirect(app_url('index.php'));
