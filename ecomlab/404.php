<?php
/**
 * 404.php — friendly "page not found" handler.
 *
 * Served by Apache via `ErrorDocument 404` (see .htaccess), and also works if
 * visited directly. Uses the shared layout so it stays on-brand, and sends a
 * real 404 status so crawlers and the browser treat it correctly.
 */
require_once __DIR__ . '/core/core.php';
http_response_code(404);

$page_title = 'Page not found';
require __DIR__ . '/views/layout/header.php';
?>
<div class="error-page">
    <div class="error-code" aria-hidden="true">404</div>
    <h1>Page not found</h1>
    <p class="sub">The page you’re looking for doesn’t exist, or may have moved.</p>

    <form class="search error-search" action="<?php echo app_url('views/search_results.php'); ?>" method="GET" role="search">
        <input type="text" name="user_query" placeholder="Search products…" autofocus>
        <button type="submit">Search</button>
    </form>

    <div class="error-actions">
        <a class="btn btn-inline" href="<?php echo app_url('index.php'); ?>">Back to home</a>
        <?php if (is_logged_in()): ?>
            <a class="btn btn-inline btn-secondary" href="<?php echo app_url('views/account/my_account.php'); ?>">My account</a>
        <?php else: ?>
            <a class="btn btn-inline btn-secondary" href="<?php echo app_url('views/login.php'); ?>">Log in</a>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/views/layout/footer.php'; ?>
