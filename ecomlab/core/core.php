<?php
/**
 * core.php — included at the top of (almost) every page.
 *
 * Responsibilities:
 *   • start output buffering so header() redirects work even after output
 *   • start the session
 *   • set the timezone
 *   • require the database base class
 *   • define shared helper functions: get_ip(), redirect(), is_logged_in(),
 *     is_admin(), require_login(), require_admin()
 *
 * MVC note: these are shared *utilities*, not business logic. No SQL, no
 * HTML output lives here.
 */

// Redirects (header('Location: ...')) fail if any output was already sent.
// Buffering lets a page redirect safely even after it has printed something.
if (!ob_get_level()) {
    ob_start();
}

// Harden the session cookie before starting the session.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,   // JS can't read the cookie (XSS defence)
        'samesite' => 'Lax',  // basic CSRF hardening
        // 'secure' => true,  // enable once the site is served over HTTPS
    ]);
    session_start();
}

date_default_timezone_set('Africa/Accra');

require_once __DIR__ . '/db_class.php';

/**
 * Append a timestamped line to error/error.log (the project's own logger).
 * Falls back to PHP's error_log() if the file can't be written.
 */
function log_error($message)
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    $file = __DIR__ . '/../error/error.log';
    if (@file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
        error_log($message);
    }
}

/**
 * Best-effort client IP address. Cart rows are keyed by IP so guests can
 * shop without an account (see Task 11).
 */
function get_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // May be a comma-separated list; take the first (original client).
        return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Redirect to $url and stop the script. Always exit after a redirect so
 * no further code runs.
 */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/** True if a customer is logged in. */
function is_logged_in()
{
    return isset($_SESSION['customer_id']);
}

/** True if the logged-in user is an admin (user_role === 1). */
function is_admin()
{
    return isset($_SESSION['user_role']) && (int) $_SESSION['user_role'] === 1;
}

/**
 * Guard for customer-only pages. Call at the very top, before any output.
 * Remembers the intended page so login can bounce the user back.
 */
function require_login()
{
    if (!is_logged_in()) {
        $_SESSION['error'] = 'Please log in to continue.';
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        redirect(app_url('views/login.php'));
    }
}

/**
 * Guard for admin-only pages. Call at the very top, before any output.
 */
function require_admin()
{
    if (!is_admin()) {
        $_SESSION['error'] = 'Admin access required.';
        redirect(app_url('index.php'));
    }
}

/**
 * Build an absolute-from-app-root URL so links work no matter how deep the
 * current script sits (views/, views/admin/, actions/, ...).
 *
 * Works under a standard document root (XAMPP htdocs → /ecomlab) AND under
 * Apache mod_userdir on the live server (→ /~andrew.quarcoo/ecomlab), where
 * the app lives outside DOCUMENT_ROOT. The base is derived by comparing the
 * running script's filesystem path (SCRIPT_FILENAME) with its URL path
 * (SCRIPT_NAME): whatever prefix of the URL maps to the app root is the base.
 */
function app_url($path = '')
{
    static $base = null;
    if ($base === null) {
        $base      = '';
        $appRootFs = str_replace('\\', '/', dirname(__DIR__)); // core/ is one level below app root
        $scriptFs  = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
        $scriptUrl = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

        // Primary: strip the script's path-below-app-root off its URL path.
        if ($scriptFs !== '' && $scriptUrl !== '' && strpos($scriptFs, $appRootFs) === 0) {
            $rel = substr($scriptFs, strlen($appRootFs)); // e.g. /views/login.php
            if ($rel !== '' && substr($scriptUrl, -strlen($rel)) === $rel) {
                $base = rtrim(substr($scriptUrl, 0, strlen($scriptUrl) - strlen($rel)), '/');
            }
        }

        // Fallback: derive from DOCUMENT_ROOT when the app sits under it.
        if ($base === '') {
            $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
            if ($docRoot !== '' && strpos($appRootFs, $docRoot) === 0) {
                $base = rtrim(str_replace($docRoot, '', $appRootFs), '/');
            }
        }
    }
    return $base . '/' . ltrim($path, '/');
}
