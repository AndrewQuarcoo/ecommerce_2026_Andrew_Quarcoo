<?php
/**
 * flash.php — renders one-time success/error messages, then clears them.
 * Include this near the top of a view's content area.
 */
if (!empty($_SESSION['success'])): ?>
    <div class="flash flash-success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
<?php unset($_SESSION['success']); endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="flash flash-error"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
<?php unset($_SESSION['error']); endif; ?>
