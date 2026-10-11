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
}
