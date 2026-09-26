<?php
/**
 * CustomerController.php — the middleman between actions and the Customer model.
 *
 * MVC note: instantiates the Model, calls its methods, returns plain
 * arrays/booleans. No SQL, no echo, no HTML here.
 */

require_once __DIR__ . '/../classes/CustomerClass.php';

class CustomerController
{
    private $customer;

    public function __construct()
    {
        $this->customer = new Customer();
    }

    /**
     * Register a new customer.
     *
     * @param array $data name,email,pass,country,city,contact,address,image
     * @return array ['success'=>true] or ['success'=>false,'error'=>'...']
     */
    public function register($data)
    {
        if ($this->customer->emailExists($data['email'])) {
            return ['success' => false, 'error' => 'Email already registered.'];
        }

        $ok = $this->customer->addCustomer(
            $data['name'],
            $data['email'],
            $data['pass'],
            $data['country'],
            $data['city'],
            $data['contact'],
            $data['address'] ?? null,
            $data['image']   ?? null
        );

        return $ok
            ? ['success' => true]
            : ['success' => false, 'error' => 'Could not create the account. Please try again.'];
    }

    /**
     * Fetch a customer's public profile (password hash removed) by email.
     * @return array|false
     */
    public function getProfileByEmail($email)
    {
        $row = $this->customer->getCustomerByEmail($email);
        if ($row) {
            unset($row['customer_pass']);
        }
        return $row;
    }

    /**
     * Attempt a login.
     *
     * @return array the customer row on success, or ['success'=>false,'error'=>'...']
     */
    public function login($email, $pass)
    {
        $row = $this->customer->login($email, $pass);
        if ($row) {
            return $row;
        }
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }
}
