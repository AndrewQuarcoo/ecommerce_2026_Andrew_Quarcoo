<?php
/**
 * CustomerClass.php — the Model for the `customer` table.
 *
 * MVC note: SQL and data logic only. No HTML, no echo, no $_POST/$_SESSION.
 * Every query that touches user input uses a prepared statement.
 */

require_once __DIR__ . '/../core/db_class.php';

class Customer extends Database
{
    /**
     * Does an account already exist for this email?
     * @return bool
     */
    public function emailExists($email)
    {
        $row = $this->fetchOne(
            'SELECT customer_email FROM customer WHERE customer_email = ?',
            [$email]
        );
        return $row !== false;
    }

    /**
     * Insert a new customer. Hashes the password here so a plain-text
     * password never leaves this method. New signups are always role 2.
     *
     * @return bool true on success
     */
    public function addCustomer($name, $email, $pass, $country, $city, $contact, $address = null, $image = null)
    {
        $hash = password_hash($pass, PASSWORD_BCRYPT);

        $sql = 'INSERT INTO customer
                    (customer_name, customer_email, customer_pass,
                     customer_country, customer_city, customer_contact,
                     customer_address, customer_image, user_role)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 2)';

        return $this->execute(
            $sql,
            [$name, $email, $hash, $country, $city, $contact, $address, $image]
        );
    }

    /**
     * Fetch one customer row by email (including the password hash, so the
     * login check can verify it).
     * @return array|false
     */
    public function getCustomerByEmail($email)
    {
        return $this->fetchOne(
            'SELECT * FROM customer WHERE customer_email = ?',
            [$email]
        );
    }

    /**
     * Verify credentials. Returns the customer row on success, false on failure.
     * @return array|false
     */
    public function login($email, $pass)
    {
        $row = $this->getCustomerByEmail($email);
        if ($row && password_verify($pass, $row['customer_pass'])) {
            return $row;
        }
        return false;
    }
}
