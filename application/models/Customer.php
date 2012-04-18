<?php

class Application_Model_Customer
{
    public $customer_id;
    public $created_at;
    public $created_in;
    public $prefix;
    public $billing;
    public $shipping;
    public $gender;
    public $firstname;
    public $lastname;
    public $dob;
    public $email;
    public $city;
    public $postcode;
    public $region;
    public $street;
    public $telephone;
    public $orders = array();
    public $price  = '' ;
    public $total_orders;

    public function __construct($customer_id)
    {
        $this->customer_id = $customer_id;
    }



}

