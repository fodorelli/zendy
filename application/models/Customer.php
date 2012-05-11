<?php

class Application_Model_Customer
{
    public $customer_id;
    public $firstname;
    public $lastname;
    public $prefix;
    public $created_at;
    public $updated_at;
    public $street;
    public $country;
    public $postcode;
    public $email;
    public $telephone;
    public $whosale_retail;
    public $subscribe_newsletter;
    public $orders = array();
    public $price  = '' ;
    public $total_orders;

    public function __construct($customer_id)
    {
        $this->customer_id = $customer_id;
    }



}

