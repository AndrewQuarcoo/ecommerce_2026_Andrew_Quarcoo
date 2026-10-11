<?php
/**
 * CartClass.php — the Model for the `cart` table (Tasks 11 & 12).
 *
 * MVC note: SQL and data logic only. No HTML, no echo, no $_POST/$_SESSION.
 * Every query that touches user input uses a prepared statement.
 *
 * Carts are keyed by IP address, not by customer id, so a guest can shop
 * without an account. Because the IP does not change when someone logs in
 * mid-session, the cart they built as a guest simply carries over.
 */

require_once __DIR__ . '/../core/db_class.php';

class Cart extends Database
{
    /**
     * Is this product already in this visitor's cart? (Task 11)
     * The handout asks for the duplicate check here rather than relying on
     * the UNIQUE (p_id, ip_add) key, so the user gets a readable message
     * instead of a constraint error.
     * @return bool
     */
    public function isInCart($productId, $ip)
    {
        $row = $this->fetchOne(
            'SELECT cart_id FROM cart WHERE p_id = ? AND ip_add = ?',
            [$productId, $ip]
        );
        return $row !== false;
    }

    /**
     * Put a product in the cart (Task 11). $customerId is recorded when the
     * visitor happens to be logged in; it stays NULL for guests.
     * @return bool true on success
     */
    public function addToCart($productId, $qty, $ip, $customerId = null)
    {
        return $this->execute(
            'INSERT INTO cart (p_id, ip_add, c_id, qty) VALUES (?, ?, ?, ?)',
            [$productId, $ip, $customerId, $qty]
        );
    }

    /**
     * Cart contents joined to the products, so the view has titles, prices
     * and images without running a second query per row (Task 11).
     * @return array list of rows (empty array if the cart is empty)
     */
    public function getCartItems($ip)
    {
        return $this->fetchAll(
            'SELECT c.cart_id, c.p_id, c.qty,
                    p.product_title, p.product_price, p.product_image
               FROM cart c
               JOIN products p ON p.product_id = c.p_id
              WHERE c.ip_add = ?
              ORDER BY c.cart_id ASC',
            [$ip]
        );
    }

    /**
     * How many distinct products are in the cart (Task 11, header summary).
     * @return int
     */
    public function getCartCount($ip)
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS c FROM cart WHERE ip_add = ?', [$ip]);
        return (int) ($row['c'] ?? 0);
    }

    /**
     * Cart value (Task 11, header summary).
     *
     * Summed in SQL rather than by looping rows in PHP: one round trip, and
     * the multiplication happens in DECIMAL arithmetic, so prices do not
     * pick up the rounding error that floats would introduce.
     * @return float
     */
    public function getCartTotal($ip)
    {
        $row = $this->fetchOne(
            'SELECT COALESCE(SUM(c.qty * p.product_price), 0) AS total
               FROM cart c
               JOIN products p ON p.product_id = c.p_id
              WHERE c.ip_add = ?',
            [$ip]
        );
        return (float) ($row['total'] ?? 0);
    }

    /**
     * Drop one product from this visitor's cart (Task 12).
     * The ip_add in the WHERE clause is the authorisation check: it stops
     * one visitor deleting a row from somebody else's cart.
     * @return bool true on success
     */
    public function removeFromCart($productId, $ip)
    {
        return $this->execute(
            'DELETE FROM cart WHERE p_id = ? AND ip_add = ?',
            [$productId, $ip]
        );
    }

    /**
     * Change a line's quantity (Task 12). Quantity must be at least 1 —
     * removing an item is removeFromCart()'s job, not a qty of 0.
     * @return bool true if the update ran
     */
    public function updateQty($productId, $newQty, $ip)
    {
        $newQty = (int) $newQty;
        if ($newQty < 1) {
            return false;
        }
        return $this->execute(
            'UPDATE cart SET qty = ? WHERE p_id = ? AND ip_add = ?',
            [$newQty, $productId, $ip]
        );
    }

    /** Empty this visitor's cart — used after a successful payment (Task 13). */
    public function clearCart($ip)
    {
        return $this->execute('DELETE FROM cart WHERE ip_add = ?', [$ip]);
    }
}
