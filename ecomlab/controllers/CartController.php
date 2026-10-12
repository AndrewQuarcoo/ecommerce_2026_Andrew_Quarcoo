<?php
/**
 * CartController.php — middleman between the cart actions/views and the model.
 *
 * MVC note: instantiates the Model and returns plain data. No SQL, no HTML.
 * Read methods return values the views can use directly; write methods return
 * ['success' => bool, 'error' => string] so an Action can flash a message.
 */

require_once __DIR__ . '/../classes/CartClass.php';
require_once __DIR__ . '/../classes/ProductClass.php';
require_once __DIR__ . '/../core/paystack.php';

class CartController
{
    private $cart;
    private $product;

    public function __construct()
    {
        $this->cart    = new Cart();
        $this->product = new Product();
    }

    // ── Reads ────────────────────────────────────────────────

    public function getCartItems($ip)
    {
        return $this->cart->getCartItems($ip);
    }

    public function getCartCount($ip)
    {
        return $this->cart->getCartCount($ip);
    }

    public function getCartTotal($ip)
    {
        return $this->cart->getCartTotal($ip);
    }

    public function isInCart($productId, $ip)
    {
        return $this->cart->isInCart($productId, $ip);
    }

    // ── Writes ───────────────────────────────────────────────

    /**
     * Task 11 — add a product to the cart.
     *
     * Checks the product actually exists before inserting: cart.p_id is a
     * foreign key, so a made-up id would otherwise fail as a raw constraint
     * error that means nothing to the visitor.
     */
    public function addToCart($productId, $qty, $ip, $customerId = null)
    {
        if (!$this->product->getProductById($productId)) {
            return ['success' => false, 'error' => 'That product could not be found.'];
        }
        if ($this->cart->isInCart($productId, $ip)) {
            return ['success' => false,
                    'error'   => 'This item is already in your cart. Go to cart to update quantity.'];
        }
        $ok = $this->cart->addToCart($productId, $qty, $ip, $customerId);
        return $ok ? ['success' => true] : ['success' => false, 'error' => 'Could not add to cart.'];
    }

    /** Task 12 — remove one product from this visitor's cart. */
    public function removeFromCart($productId, $ip)
    {
        $ok = $this->cart->removeFromCart($productId, $ip);
        return $ok ? ['success' => true] : ['success' => false, 'error' => 'Could not remove that item.'];
    }

    /** Task 12 — set a line's quantity (must be 1 or more). */
    public function updateQty($productId, $newQty, $ip)
    {
        if ((int) $newQty < 1) {
            return ['success' => false, 'error' => 'Quantity must be at least 1.'];
        }
        $ok = $this->cart->updateQty($productId, $newQty, $ip);
        return $ok ? ['success' => true] : ['success' => false, 'error' => 'Could not update that quantity.'];
    }

    public function clearCart($ip)
    {
        return $this->cart->clearCart($ip);
    }

    // ── Orders & payment (Task 13) ───────────────────────────

    public function processPayment($customerId, $ip, $reference, $amount)
    {
        return $this->cart->processPayment($customerId, $ip, $reference, $amount);
    }

    /**
     * Find and record a payment that succeeded but never reached us.
     *
     * The gap this closes: the customer pays, then closes the tab before
     * Paystack's callback redirects them back. The charge went through, but
     * the browser never told us, so no order exists — and their cart is still
     * full, so the obvious next thing they do is pay a second time.
     *
     * So before offering the Pay button again, ask Paystack whether this
     * customer already has a successful charge we have not recorded. The
     * amount must equal the current cart total: if they changed the cart
     * after paying, the old charge no longer corresponds to what is in front
     * of them, and silently turning it into an order for different goods
     * would be worse than asking them to pay.
     *
     * A controller is the right home for this — it coordinates the Paystack
     * client (core/paystack.php) and the model, and contains no SQL itself.
     *
     * @return int|null the recovered order id, or null if there was nothing
     */
    public function recoverUnrecordedPayment($customerId, $email, $ip)
    {
        if (!paystack_configured()) {
            return null;
        }

        $total = round($this->getCartTotal($ip), 2);
        if ($total <= 0) {
            return null;                       // nothing to reconcile against
        }
        $expectedMinor = (int) round($total * 100);

        foreach (paystack_recent_successful($email) as $tx) {
            if ($tx['reference'] === '' || $tx['currency'] !== PAYSTACK_CURRENCY) {
                continue;
            }
            if ($tx['amount'] !== $expectedMinor) {
                continue;                      // not this cart
            }
            if ($this->getPaymentByReference($tx['reference'])) {
                continue;                      // already recorded
            }

            $orderId = $this->processPayment($customerId, $ip, $tx['reference'], $total);
            if ($orderId) {
                log_error('Recovered an unrecorded payment: order ' . $orderId
                          . ' from ref=' . $tx['reference']);
                return (int) $orderId;
            }
        }
        return null;
    }

    public function getPaymentByReference($reference)
    {
        return $this->cart->getPaymentByReference($reference);
    }

    public function getOrdersByCustomer($customerId)
    {
        return $this->cart->getOrdersByCustomer($customerId);
    }

    public function getOrderById($orderId)
    {
        return $this->cart->getOrderById($orderId);
    }

    public function getOrderItems($orderId)
    {
        return $this->cart->getOrderItems($orderId);
    }
}
