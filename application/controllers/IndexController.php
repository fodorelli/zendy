<?php

class IndexController extends Zend_Controller_Action
{
    private $day = '';

    public $soap;
    public $session_id;



    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        
    }

    public function soapAction()
    {
		$soap = new SoapClient('http://alexandalexa.dev/api/soap/?wsdl');
		$session_id = $soap->login('alexa-api', 'goaway');

		$orderModel = new Application_Model_Orders();
        $orders = $orderModel->getOrderByDate('2012-01-01');



        $last_time ='';
		$filters = array('created_at' => array('gt' => $last_time),'status' => array('eq' => 'canceled'));

		//$result  = $soap->call($session_id, 'sales_order.list', $filters);
		$result = $soap->call($session_id, 'sales_order.info', '100050022');
		
		var_dump($result);
		die;
        
    }

    public function listAllAction()
    {
        // action body
    }

    public function filter($orders)
    {
    }

    public function rangeDateAction()
    {
        $start = '2012-01-01 10:52:22';
        $to = '2012-04-09 10:52:22';

        $this->view->start = $start;
        $this->view->to = $to;

        $this->soap = new SoapClient('http://alexandalexa.dev/api/soap/?wsdl');
        $this->session_id = $this->soap->login('alexa-api', 'goaway');
        $ordersModel = new Application_Model_Orders();
        $productMapper = new Application_Model_ProductMapper();
        $filters = array('created_at' => array(
            'from' => $start,
            'to'  => $to));

        $result = $this->soap->call($this->session_id, 'sales_order.list', array($filters));

        foreach ($result as $order_arr) {

            $order = new Zend_Config($order_arr);
            $orderModel = new Application_Model_Order(); //var_dump($order);die;
            $orderModel->created_at = $order->created_at;
            $orderModel->customer_id = $order->customer_id;
            $orderModel->customer_firstname = $order->customer_firstname;
            $orderModel->customer_lastname = $order->customer_lastname;
            $orderModel->customer_email = $order->customer_email;
            $orderModel->status = $order->status;
            $orderModel->grand_total = $order->grand_total;
            $orderModel->shipping_ammount = $order->shipping_amount;
            $orderModel->qty = (int)$order->total_qty_ordered;
            $orderModel->weight = (int)$order->weight;


            $orderInfo = $this->getOrderInfo($order->increment_id);

            foreach($orderInfo['items'] as $product_arr){

                $product = new Zend_Config($product_arr);
                $productModel = new Application_Model_Product();

                $productModel->created_at = $product->created_at;
                $productModel->description = $product->description;
                $productModel->name = $product->name;
                $productModel->price = $product->price;
                $productModel->product_id = $product->product_id;
                $productModel->sku = $product->sku;
                $productModel->tax_ammount = $product->tax_ammount;
                $productModel->weight = $product->weight;

                $productMapper->products[] = $productModel;


            }


            $ordersModel->orders[] = $orderModel;

            //@todo remove the break
            //break;

        }

        $dailyMapper = new Application_Model_DailyMapper();
        $customerMapper = new Application_Model_CustomerMapper();
        $fp = fopen('order.csv', 'w');
        fputs($fp,'Create At, Customer Id, First Name, Last Name, Email, Status, Grand Total, Shipping Ammount, Quantity, Weight' . PHP_EOL);
        foreach($ordersModel->orders as $one){

            $ex = array();
            $ex['created_at'] = $one->created_at;
            $ex['customer_id'] = $one->customer_id;
            $ex['customer_firstname'] = $one->customer_firstname;
            $ex['customer_lastname'] = $one->customer_lastname;
            $ex['customer_email'] = $one->customer_email;
            $ex['status'] = $one->status;
            $ex['grand_total'] = $one->grand_total;
            $ex['shipping_ammount'] = $one->shipping_ammount;
            $ex['qty'] = $one->qty;
            $ex['weight'] = $one->weight;

            $customer = $this->getCustomerData($one->customer_id);
            $customer = new Zend_Config($customer);

            $addressCustomer = $this->getCustomerAddress($customer->customer_id);
            $addressCustomer = new Zend_Config($addressCustomer[0]);

            $customerModel = new Application_Model_Customer($customer->customer_id);

            $customerModel->created_at  = $customer->created_at;
            $customerModel->created_in  = $customer->created_in;
            $customerModel->prefix      = $customer->prefix;
            $customerModel->firstname   = $customer->firstname;
            $customerModel->lastname    = $customer->lastname;
            $customerModel->dob         = $customer->dob;
            $customerModel->email       = $customer->email;
            $customerModel->billing     = $customer->default_billing;
            $customerModel->shipping    = $customer->default_shipping;
            $customerModel->gender      = $customer->gender;
            $customerModel->city        = $addressCustomer->city;
            $customerModel->postcode    = $addressCustomer->postcode;
            $customerModel->region      = $addressCustomer->region;
            $customerModel->street      = $addressCustomer->street;
            $customerModel->telephone   = $addressCustomer->telephone;

            $customerMapper->customers[] = $customerModel;

            fputcsv($fp, $ex);
        }
        fclose($fp);

        /**
         * Create Customers
         */
        $fp = fopen('customers.csv', 'w');
        fputs($fp, 'Customer Id, Crated At, Create In, Prefix, Billing, Shipping, Gender, Firstname, Lastname, Date Of Birth, Email, City, Postcode, Region, Street, Telephone' . PHP_EOL);
        foreach ($customerMapper->customers as $one) {
            $ex = array();
            $ex['customer_id'] = $one->customer_id;
            $ex['created_at'] = $one->created_at;
            $ex['created_in'] = $one->created_in;
            $ex['prefix'] = $one->prefix;
            $ex['billing'] = $one->billing;
            $ex['shipping'] = $one->shipping;
            $ex['gender'] = $one->gender;
            $ex['firstname'] = $one->firstname;
            $ex['lastname'] = $one->lastname;
            $ex['dob'] = $one->dob;
            $ex['email'] = $one->email;
            $ex['city'] = $one->city;
            $ex['postcode'] = $one->postcode;
            $ex['region'] = $one->region;
            $ex['street'] = $one->street;
            $ex['telephone'] = $one->telephone;

            fputcsv($fp, $ex);
        }
        fclose($fp);


        /**
         * Create Products
         */

        $fp = fopen('products.csv', 'w');
        fputs($fp, 'Product Id, Weight, SKU, Name, Description, Price, Tax ammount, Created At' . PHP_EOL);

        foreach ($productMapper->products as $one) {

            $ex = array();
            $ex['product_id'] = $one->product_id;
            $ex['weight'] = $one->weight;
            $ex['sku'] = $one->sku;
            $ex['name'] = $one->name;
            $ex['description'] = $one->description;
            $ex['price'] = $one->price;
            $ex['tax_ammount'] = $one->tax_ammount;
            $ex['created_at'] = $one->created_at;

            fputcsv($fp, $ex);

        }
        fclose($fp);



        /**
         * Get order by specific date
         */
        //$this->getStatistics($result);

        $this->view->orders = count($result);
    }

    public function getCustomerData($customer_id)
    {
        $result = $this->soap->call($this->session_id, 'customer.info', $customer_id);

        return $result;

    }

    public function getStatistics($result)
    {
        $skip = array();
        foreach ($result as $order_arr) {

            $order = new Zend_Config($order_arr);
            $day_created = new Zend_Date($order->created_at);echo $day_created->toString();
            $start = clone $day_created->set(12, Zend_Date::HOUR);
            $end = clone $day_created->add(1, Zend_Date::DAY);

            //echo $start->toString('yyyy-MM-dd HH-mm-ss');die;


            $filters = array(array('created_at' => array('from' => $start->toString('yyyy-MM-dd HH:mm:ss'), 'to' => $end->toString('yyyy-MM-dd HH:mm:ss'))));
            $results = $this->soap->call($this->session_id, 'sales_order.list', $filters);
            echo '</br>' . count($results);
        }
    }

    public function getCustomerAddress($customer_id)
    {
        return $this->soap->call($this->session_id, 'customer_address.list', $customer_id);

    }

    public function getOrderInfo($order_id)
    {
        return $this->soap->call($this->session_id, 'sales_order.info', $order_id);
    }

}