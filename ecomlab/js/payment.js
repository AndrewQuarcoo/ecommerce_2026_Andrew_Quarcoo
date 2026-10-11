/* ============================================================
   payment.js — Paystack inline checkout (Task 13).

   The handout puts this in an inline <script> with PHP echoed straight into
   it. It lives in its own file instead for two reasons:
     • the site's Content-Security-Policy forbids inline script, which is the
       single most effective anti-XSS rule we have, and carving a hole in it
       for every page would throw that away;
     • echoing PHP into JavaScript means escaping for a second language, and
       getting that wrong is its own injection bug.
   The values arrive as data- attributes instead, already HTML-escaped.
   ============================================================ */

const payButton = document.getElementById('pay-button');

if (payButton && window.PaystackPop) {
    payButton.addEventListener('click', function () {
        const key      = payButton.dataset.key;
        const email    = payButton.dataset.email;
        const amount   = parseInt(payButton.dataset.amount, 10); // already in pesewas
        const currency = payButton.dataset.currency;
        const callback = payButton.dataset.callback;

        if (!key || !email || !amount || amount < 1) {
            showPayError('Payment is not configured correctly. Please contact us.');
            return;
        }

        payButton.disabled = true;
        payButton.textContent = 'Opening Paystack…';

        const handler = PaystackPop.setup({
            key: key,
            email: email,
            amount: amount,
            currency: currency,
            onSuccess: function (response) {
                // The server re-verifies this reference with Paystack before
                // recording anything, so a forged call here achieves nothing.
                window.location = callback + '?ref=' + encodeURIComponent(response.reference);
            },
            onCancel: function () {
                payButton.disabled = false;
                payButton.textContent = 'Pay with Paystack';
                showPayError('Payment cancelled.');
            },
        });
        handler.openIframe();
    });
} else if (payButton) {
    // The external script was blocked or failed to load.
    payButton.disabled = true;
    showPayError('Could not load the payment library. Check your connection and refresh.');
}

function showPayError(message) {
    const box = document.getElementById('pay-error');
    if (box) {
        box.textContent = message;
    }
}
