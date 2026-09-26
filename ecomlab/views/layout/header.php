<?php
/**
 * header.php — shared page head + navigation, included at the top of every view.
 *
 * MVC note: this is the View's layout layer. Only HTML and minimal PHP for
 * session-based nav (show Logout when logged in). No SQL here.
 *
 * Assumes core/core.php has already been required by the including page, so
 * is_logged_in(), is_admin() and app_url() are available.
 */
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/../../core/core.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' — shoppn' : 'shoppn'; ?></title>
    <link rel="stylesheet" href="<?php echo app_url('css/style.css'); ?>">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?php echo app_url('index.php'); ?>">shoppn</a>

        <form class="search" action="<?php echo app_url('views/search_results.php'); ?>" method="GET" role="search">
            <input type="text" name="user_query" placeholder="Search products…"
                   value="<?php echo htmlspecialchars($_GET['user_query'] ?? ''); ?>">
            <button type="submit">Search</button>
        </form>

        <nav class="main-nav">
            <a href="<?php echo app_url('index.php'); ?>">Home</a>

            <?php if (is_admin()): ?>
                <a href="<?php echo app_url('views/admin/brand.php'); ?>">Brands</a>
                <a href="<?php echo app_url('views/admin/category.php'); ?>">Categories</a>
            <?php endif; ?>

            <?php if (is_logged_in()): ?>
                <span class="nav-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'friend'); ?></span>
                <a href="<?php echo app_url('views/account/my_account.php'); ?>">My Account</a>
                <a class="nav-cta" href="<?php echo app_url('logout.php'); ?>">Logout</a>
            <?php else: ?>
                <a href="<?php echo app_url('views/register.php'); ?>">Register</a>
                <a class="nav-cta" href="<?php echo app_url('views/login.php'); ?>">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="page">
