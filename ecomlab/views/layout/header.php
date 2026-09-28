<?php
/**
 * header.php — shared page head + navigation (decor theme).
 *
 * MVC note: View layout layer — HTML + minimal session-aware PHP only, no SQL.
 *
 * Pages may set, before including this file:
 *   $page_title       — <title> text
 *   $transparent_nav  — true to overlay the nav on a hero (home page)
 *   $full_bleed       — true to drop the centered .page wrapper (home page)
 */
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/../../core/core.php';
}
$transparent_nav = $transparent_nav ?? false;
$full_bleed      = $full_bleed ?? false;

/**
 * The bowtie mark that sits between the two halves of the logo: one solid
 * shape with straight vertical ends and concave top/bottom edges pinching to
 * a waist in the middle — not two separate triangles.
 */
function bowtie_svg($class = 'bowtie') {
    return '<svg class="' . $class . '" viewBox="0 0 40 24" aria-hidden="true">'
         . '<path d="M2 2 Q20 9 38 2 L38 22 Q20 15 2 22 Z" fill="currentColor"/>'
         . '</svg>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' — shoppn' : 'shoppn — Create a space that’s uniquely you'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,900;1,9..144,400&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo app_url('css/style.css'); ?>">
</head>
<body class="<?php echo $transparent_nav ? 'has-hero' : ''; ?>">
<header id="siteNav" class="site-nav <?php echo $transparent_nav ? 'nav-over-hero' : 'nav-solid'; ?>">
    <div class="nav-inner">
        <nav class="nav-left" aria-label="Primary">
            <a href="<?php echo app_url('index.php#dream'); ?>">Shop</a>
            <a href="<?php echo app_url('index.php#sustainable'); ?>">About</a>
            <a href="<?php echo app_url('index.php#gallery'); ?>">Inspiration</a>
            <?php if (is_admin()): ?>
                <a href="<?php echo app_url('views/admin/brand.php'); ?>">Brands</a>
                <a href="<?php echo app_url('views/admin/category.php'); ?>">Categories</a>
            <?php endif; ?>
        </nav>

        <a class="logo" href="<?php echo app_url('index.php'); ?>" aria-label="shoppn home">
            <span>SHOP</span><?php echo bowtie_svg(); ?><span>PN</span>
        </a>

        <div class="nav-right">
            <a class="nav-link icon-link" href="<?php echo app_url('views/search_results.php'); ?>">
                <svg viewBox="0 0 24 24" class="ic" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><line x1="16.5" y1="16.5" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <span>Search</span>
            </a>
            <a class="nav-link icon-link" href="<?php echo app_url('views/cart.php'); ?>">
                <svg viewBox="0 0 24 24" class="ic" aria-hidden="true"><path d="M3 4h2l2.4 12.2a1 1 0 0 0 1 .8h9.2a1 1 0 0 0 1-.8L21 8H6" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/><circle cx="10" cy="20" r="1.4" fill="currentColor"/><circle cx="18" cy="20" r="1.4" fill="currentColor"/></svg>
                <span>Cart</span>
            </a>
            <?php if (is_logged_in()): ?>
                <a class="nav-link" href="<?php echo app_url('views/account/my_account.php'); ?>">Account</a>
                <a class="nav-link" href="<?php echo app_url('logout.php'); ?>">Logout</a>
            <?php else: ?>
                <a class="nav-link" href="<?php echo app_url('views/login.php'); ?>">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="<?php echo $full_bleed ? 'main-fluid' : 'page'; ?>">
