<?php
/**
 * register_action.php — processes the registration form.
 *
 * MVC note: an Action reads input, validates it server-side, calls the
 * Controller, sets session, and redirects. No SQL, no HTML output.
 */

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

// Only ever run on a POST submission.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_url('views/register.php'));
}

// Reject forged cross-site submissions.
if (!verify_csrf()) {
    redirect(app_url('views/register.php'));
}

/** Trim + strip tags on a posted field. */
function clean($key)
{
    return trim(strip_tags($_POST[$key] ?? ''));
}

$name    = clean('customer_name');
$email   = clean('customer_email');
$pass    = $_POST['customer_pass'] ?? '';   // not stripped — password may contain symbols
$country = clean('customer_country');
$city    = clean('customer_city');
$contact = clean('customer_contact');
$address = clean('customer_address');

// ── Server-side validation (JS validation can always be bypassed) ──
$errors = [];

if ($name === '' || mb_strlen($name) > 100) {
    $errors[] = 'Name is required (max 100 characters).';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 50) {
    $errors[] = 'A valid email is required (max 50 characters).';
}
// Password strength. The same rule is enforced in js/validate.js for instant
// feedback, but this server-side check is the one that actually protects the
// account: JS can always be bypassed. Each requirement is reported separately
// so the user knows exactly what to fix.
// Upper bound is 72 bytes because bcrypt silently ignores anything past that
// — better to reject a too-long password than to store a truncated one.
$passErrors = [];
if (strlen($pass) < 8) {
    $passErrors[] = '8 characters';
}
if (!preg_match('/[a-z]/', $pass)) {
    $passErrors[] = 'a lowercase letter';
}
if (!preg_match('/[A-Z]/', $pass)) {
    $passErrors[] = 'an uppercase letter';
}
if (!preg_match('/\d/', $pass)) {
    $passErrors[] = 'a number';
}
if (!preg_match('/[^A-Za-z0-9]/', $pass)) {
    $passErrors[] = 'a special character';
}
if (strlen($pass) > 72) {
    $errors[] = 'Password must be 72 characters or fewer.';
} elseif ($passErrors) {
    $errors[] = 'Password needs at least ' . implode(', ', $passErrors) . '.';
}
// Country must be one of the values offered by the form (whitelist).
$allowedCountries = ['Ghana', 'Nigeria', 'Kenya', 'South Africa', 'United States',
                     'United Kingdom', 'Canada', 'Germany', 'France', 'India', 'Other'];
if (!in_array($country, $allowedCountries, true)) {
    $errors[] = 'Please select a valid country.';
}
if ($city === '' || mb_strlen($city) > 30) {
    $errors[] = 'City is required.';
}
if (!preg_match('/^[0-9+\-\s]{7,15}$/', $contact)) {
    $errors[] = 'Contact must be 7–15 digits (may include + - spaces).';
}
if (mb_strlen($address) > 255) {
    $errors[] = 'Address is too long (max 255 characters).';
}

// ── Optional profile image upload ──
$imageName = null;
if (!empty($_FILES['customer_image']['name']) && $_FILES['customer_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['customer_image'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Image upload failed. Please try a different file.';
    } else {
        // Map allowed MIME types to a safe, server-chosen extension. The
        // extension is NEVER taken from the user-supplied filename — that
        // prevents saving an executable name (e.g. "evil.php") that a real
        // image could otherwise smuggle past the MIME check.
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowed[$mime])) {
            $errors[] = 'Profile image must be JPG, PNG, GIF or WEBP.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Profile image must be 2 MB or smaller.';
        } else {
            $imageName = uniqid('cust_', true) . '.' . $allowed[$mime];
            $dest      = __DIR__ . '/../images/customers/' . $imageName;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $errors[] = 'Could not save the profile image.';
                $imageName = null;
            }
        }
    }
}

// Preserve entered values (except password) so the form can be re-filled.
$_SESSION['old'] = [
    'customer_name'    => $name,
    'customer_email'   => $email,
    'customer_country' => $country,
    'customer_city'    => $city,
    'customer_contact' => $contact,
    'customer_address' => $address,
];

if ($errors) {
    $_SESSION['error'] = implode(' ', $errors);
    redirect(app_url('views/register.php'));
}

// ── Hand off to the controller ──
$controller = new CustomerController();
$result = $controller->register([
    'name'    => $name,
    'email'   => $email,
    'pass'    => $pass,
    'country' => $country,
    'city'    => $city,
    'contact' => $contact,
    'address' => $address,
    'image'   => $imageName,
]);

if (!empty($result['success'])) {
    unset($_SESSION['old']);

    // Log the new customer straight in.
    $customer = (new CustomerController())->login($email, $pass);
    if (isset($customer['customer_id'])) {
        regenerate_session(); // defeat session fixation on privilege change
        $_SESSION['customer_id']    = $customer['customer_id'];
        $_SESSION['customer_name']  = $customer['customer_name'];
        $_SESSION['customer_email'] = $customer['customer_email'];
        $_SESSION['user_role']      = $customer['user_role'];
    }
    $_SESSION['success'] = 'Welcome to shoppn, ' . $name . '!';
    redirect(app_url('views/account/my_account.php'));
}

$_SESSION['error'] = $result['error'] ?? 'Registration failed.';
redirect(app_url('views/register.php'));
