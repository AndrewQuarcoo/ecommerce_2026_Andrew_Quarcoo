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

    // ── Orders & payment (Task 13) ───────────────────────────

    /**
     * Turn the cart into a paid order, atomically.
     *
     * Five steps that must all happen or none: generate an invoice number,
     * record the order, copy the cart lines into orderdetails, record the
     * payment, then empty the cart. A transaction is what makes "or none"
     * true — without it, a failure half way through could take the customer's
     * money and leave them with no order, or clear the cart with nothing
     * recorded.
     *
     * The caller must already have verified the payment with Paystack.
     *
     * @return int|false the new order id, or false if anything failed
     */
    public function processPayment($customerId, $ip, $reference, $amount)
    {
        $conn = $this->getConnection();
        $conn->beginTransaction();

        try {
            $items = $this->getCartItems($ip);
            if (!$items) {
                $conn->rollBack();
                return false;
            }

            // invoice_no is UNIQUE, so a random collision must be retried
            // rather than allowed to abort the whole order.
            $invoice = null;
            for ($try = 0; $try < 10; $try++) {
                $candidate = mt_rand(100000, 999999);
                $taken = $this->fetchOne('SELECT order_id FROM orders WHERE invoice_no = ?', [$candidate]);
                if ($taken === false) {
                    $invoice = $candidate;
                    break;
                }
            }
            if ($invoice === null) {
                $conn->rollBack();
                return false;
            }

            $this->execute(
                'INSERT INTO orders (customer_id, invoice_no, order_date, order_status)
                 VALUES (?, ?, CURDATE(), ?)',
                [$customerId, $invoice, 'paid']
            );
            $orderId = (int) $conn->lastInsertId();

            foreach ($items as $item) {
                $this->execute(
                    'INSERT INTO orderdetails (order_id, product_id, qty) VALUES (?, ?, ?)',
                    [$orderId, $item['p_id'], $item['qty']]
                );
            }

            $this->execute(
                'INSERT INTO payment (amt, customer_id, order_id, currency, reference, payment_date)
                 VALUES (?, ?, ?, ?, ?, CURDATE())',
                [$amount, $customerId, $orderId, PAYSTACK_CURRENCY, $reference]
            );

            $this->execute('DELETE FROM cart WHERE ip_add = ?', [$ip]);

            $conn->commit();
            return $orderId;
        } catch (Exception $e) {
            $conn->rollBack();
            log_error('processPayment failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Has this Paystack reference already been recorded? Verifying a
     * reference twice would otherwise create a second order for one payment,
     * which is what happens if the customer refreshes the callback URL.
     * @return array|false the existing payment row, or false
     */
    public function getPaymentByReference($reference)
    {
        return $this->fetchOne('SELECT * FROM payment WHERE reference = ?', [$reference]);
    }

    /**
     * Every order this customer has placed, newest first, with its value and
     * item count — the data behind the order history page.
     *
     * The payment columns are wrapped in MAX() rather than selected raw.
     * payment.order_id is indexed but not UNIQUE, so under ONLY_FULL_GROUP_BY
     * MySQL cannot prove there is just one payment row per order and would
     * reject the query. MAX() over a single row is that row.
     *
     * @return array list of rows (empty array if the customer has none)
     */
    public function getOrdersByCustomer($customerId)
    {
        return $this->fetchAll(
            'SELECT o.order_id, o.invoice_no, o.order_date, o.order_status,
                    MAX(p.amt)       AS amt,
                    MAX(p.currency)  AS currency,
                    MAX(p.reference) AS reference,
                    COUNT(d.product_id)                      AS item_count,
                    COALESCE(SUM(d.qty * pr.product_price),0) AS total
               FROM orders o
          LEFT JOIN payment      p  ON p.order_id   = o.order_id
          LEFT JOIN orderdetails d  ON d.order_id   = o.order_id
          LEFT JOIN products     pr ON pr.product_id = d.product_id
              WHERE o.customer_id = ?
           GROUP BY o.order_id, o.invoice_no, o.order_date, o.order_status
           ORDER BY o.order_id DESC',
            [$customerId]
        );
    }

    /**
     * One order header, with the customer's details for the invoice.
     * @return array|false
     */
    public function getOrderById($orderId)
    {
        return $this->fetchOne(
            'SELECT o.*, p.amt, p.currency, p.reference, p.payment_date,
                    c.customer_name, c.customer_email, c.customer_city, c.customer_country
               FROM orders o
               JOIN customer c ON c.customer_id = o.customer_id
          LEFT JOIN payment  p ON p.order_id    = o.order_id
              WHERE o.order_id = ?',
            [$orderId]
        );
    }

    /**
     * The line items of one order, with product names and the price each.
     * @return array list of rows (empty array if none)
     */
    public function getOrderItems($orderId)
    {
        return $this->fetchAll(
            'SELECT d.product_id, d.qty, p.product_title, p.product_price, p.product_image
               FROM orderdetails d
               JOIN products p ON p.product_id = d.product_id
              WHERE d.order_id = ?',
            [$orderId]
        );
    }
}
