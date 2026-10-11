/* ============================================================
   validate.js — client-side form validation.
   Fast, friendly feedback in the browser. The server validates
   again (actions/*.php) because JS can always be bypassed.
   ============================================================ */

// ── Shared regex patterns ──
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const phoneRegex = /^[0-9+\-\s]{7,15}$/;
// Strong password: >=8 chars AND at least one lowercase, one uppercase,
// one digit and one special character. The lookaheads each assert "somewhere
// in the string there is one of these" without consuming any characters, so
// they can all apply to the same 8+ characters. This rejects weak-but-long
// passwords like "12345678" or "password".
const passRegex  = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,72}$/;
const nameRegex  = /^.{2,100}$/;

const IMG_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
const IMG_MAX   = 2 * 1024 * 1024; // 2 MB

/** Show/clear an inline error and toggle the invalid style on the input. */
function setError(inputId, errorId, message) {
    const input = document.getElementById(inputId);
    const box   = document.getElementById(errorId);
    if (box) box.textContent = message || '';
    if (input) input.classList.toggle('input-invalid', !!message);
    return !message;
}

/** Attach live-clearing: once a user fixes a field, drop its error. */
function liveClear(inputId, errorId, validator) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('input', function () {
        if (validator(input.value)) setError(inputId, errorId, '');
    });
}

// ── Registration form ──
const registerForm = document.getElementById('register-form');
if (registerForm) {
    const v = {
        name:    (val) => nameRegex.test(val.trim()),
        email:   (val) => emailRegex.test(val.trim()),
        pass:    (val) => passRegex.test(val),
        country: (val) => val.trim() !== '',
        city:    (val) => val.trim() !== '',
        contact: (val) => phoneRegex.test(val.trim()),
    };
    liveClear('customer_name', 'error-name', v.name);
    liveClear('customer_email', 'error-email', v.email);
    liveClear('customer_pass', 'error-pass', v.pass);
    liveClear('customer_city', 'error-city', v.city);
    liveClear('customer_contact', 'error-contact', v.contact);

    registerForm.addEventListener('submit', function (e) {
        let ok = true;
        const val = (id) => document.getElementById(id).value;

        ok &= setError('customer_name', 'error-name',
            v.name(val('customer_name')) ? '' : 'Enter your full name (2–100 characters).');
        ok &= setError('customer_email', 'error-email',
            v.email(val('customer_email')) ? '' : 'Enter a valid email address.');
        ok &= setError('customer_pass', 'error-pass',
            v.pass(val('customer_pass')) ? '' : passMessage(val('customer_pass')));
        ok &= setError('customer_country', 'error-country',
            v.country(val('customer_country')) ? '' : 'Please select a country.');
        ok &= setError('customer_city', 'error-city',
            v.city(val('customer_city')) ? '' : 'City is required.');
        ok &= setError('customer_contact', 'error-contact',
            v.contact(val('customer_contact')) ? '' : 'Enter 7–15 digits (may include + - spaces).');

        // Optional image: validate only if one is chosen.
        const imgInput = document.getElementById('customer_image');
        if (imgInput && imgInput.files.length > 0) {
            const f = imgInput.files[0];
            let imgMsg = '';
            if (!IMG_TYPES.includes(f.type)) imgMsg = 'Image must be JPG, PNG, GIF or WEBP.';
            else if (f.size > IMG_MAX)       imgMsg = 'Image must be 2 MB or smaller.';
            ok &= setError('customer_image', 'error-image', imgMsg);
        }

        if (!ok) {
            e.preventDefault();
            const firstError = registerForm.querySelector('.input-invalid');
            if (firstError) firstError.focus();
        } else {
            showLoading('register-submit', 'Creating account…');
        }
    });
}

/**
 * Name the requirements a password is still missing, so the user is told
 * exactly what to fix rather than just "invalid password".
 */
function passMessage(pass) {
    const missing = [];
    if (pass.length < 8)          missing.push('8 characters');
    if (!/[a-z]/.test(pass))      missing.push('a lowercase letter');
    if (!/[A-Z]/.test(pass))      missing.push('an uppercase letter');
    if (!/\d/.test(pass))         missing.push('a number');
    if (!/[^A-Za-z0-9]/.test(pass)) missing.push('a special character (e.g. !?$#)');
    if (pass.length > 72)         return 'Password must be 72 characters or fewer.';
    return 'Password needs at least ' + missing.join(', ') + '.';
}

// ── Admin product form (Task 9) ──
// Client-side checks mirror actions/product_input.php and the PHP upload
// rules. They are a convenience only — the server validates everything
// again, because JS can be bypassed and a file input can be scripted.
const productForm = document.getElementById('product-form');
if (productForm) {
    const priceRegex = /^\d{1,8}(\.\d{1,2})?$/;   // DECIMAL(10,2), not negative

    const pv = {
        title: (val) => val.trim().length >= 2 && val.trim().length <= 200,
        cat:   (val) => val !== '',
        brand: (val) => val !== '',
        price: (val) => priceRegex.test(val.trim()),
        desc:  (val) => val.length <= 500,
        keys:  (val) => val.length <= 100,
    };
    liveClear('product_title',    'error-product-title',    pv.title);
    liveClear('product_cat',      'error-product-cat',      pv.cat);
    liveClear('product_brand',    'error-product-brand',    pv.brand);
    liveClear('product_price',    'error-product-price',    pv.price);
    liveClear('product_desc',     'error-product-desc',     pv.desc);
    liveClear('product_keywords', 'error-product-keywords', pv.keys);

    productForm.addEventListener('submit', function (e) {
        let ok = true;
        const val = (id) => (document.getElementById(id) || {}).value || '';

        ok &= setError('product_title', 'error-product-title',
            pv.title(val('product_title')) ? '' : 'Title is required (2–200 characters).');
        ok &= setError('product_cat', 'error-product-cat',
            pv.cat(val('product_cat')) ? '' : 'Please choose a category.');
        ok &= setError('product_brand', 'error-product-brand',
            pv.brand(val('product_brand')) ? '' : 'Please choose a brand.');
        ok &= setError('product_price', 'error-product-price',
            pv.price(val('product_price')) ? '' : 'Enter a price like 249.99 (no negatives).');
        ok &= setError('product_desc', 'error-product-desc',
            pv.desc(val('product_desc')) ? '' : 'Description must be 500 characters or fewer.');
        ok &= setError('product_keywords', 'error-product-keywords',
            pv.keys(val('product_keywords')) ? '' : 'Keywords must be 100 characters or fewer.');

        // The image is required when adding (the input carries `required`),
        // optional when editing. Validate type and size whenever one is chosen.
        const imgInput = document.getElementById('product_image');
        if (imgInput) {
            let imgMsg = '';
            if (imgInput.files.length > 0) {
                const f = imgInput.files[0];
                if (!IMG_TYPES.includes(f.type)) imgMsg = 'Image must be JPG, PNG, GIF or WEBP.';
                else if (f.size > IMG_MAX)       imgMsg = 'Image must be 2 MB or smaller.';
            } else if (imgInput.hasAttribute('required')) {
                imgMsg = 'A product image is required.';
            }
            ok &= setError('product_image', 'error-product-image', imgMsg);
        }

        if (!ok) {
            e.preventDefault();
            const firstError = productForm.querySelector('.input-invalid');
            if (firstError) firstError.focus();
        } else {
            showLoading('product-submit', 'Saving…');
        }
    });
}

// ── Login form ──
const loginForm = document.getElementById('login-form');
if (loginForm) {
    liveClear('login_email', 'error-login-email', (val) => emailRegex.test(val.trim()));
    liveClear('login_pass', 'error-login-pass', (val) => val.length > 0);

    loginForm.addEventListener('submit', function (e) {
        let ok = true;
        const email = document.getElementById('login_email').value;
        const pass  = document.getElementById('login_pass').value;

        ok &= setError('login_email', 'error-login-email',
            emailRegex.test(email.trim()) ? '' : 'Enter a valid email address.');
        ok &= setError('login_pass', 'error-login-pass',
            pass.length > 0 ? '' : 'Password is required.');

        if (!ok) {
            e.preventDefault();
        } else {
            showLoading('login-submit', 'Logging in…');
        }
    });
}

/** Disable a submit button and show progress text. */
function showLoading(btnId, text) {
    const btn = document.getElementById(btnId);
    if (btn) {
        btn.disabled = true;
        btn.dataset.original = btn.textContent;
        btn.textContent = text;
    }
}
