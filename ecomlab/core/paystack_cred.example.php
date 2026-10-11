<?php
/**
 * paystack_cred.example.php — copy to paystack_cred.php and fill in.
 *
 * The real file is git-ignored, exactly like db_cred.php: a secret key can
 * charge and refund money, and this repository is public.
 *
 * Get free test keys from https://dashboard.paystack.com/#/settings/developer
 * Test card: 4084 0840 8408 4081, any future expiry, CVV 408.
 *
 *   cp core/paystack_cred.example.php core/paystack_cred.php
 */

// Public key — safe to appear in page source; it only starts a transaction.
define('PAYSTACK_PUBLIC_KEY', 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

// Secret key — server-side only. NEVER send this to the browser.
define('PAYSTACK_SECRET_KEY', 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

// Currency the store charges in. Must match the shop's prices.
define('PAYSTACK_CURRENCY', 'GHS');
