<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cdiacon
 * Date: 08/05/2012
 * Time: 11:16
 * update information in db from csv files
 */
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

//ini_set('memory_limit','512M');
ini_set('memory_limit', '-1');
ini_set('default_socket_timeout', 180);
set_time_limit(0);

$orderModel = new Application_Model_Order();
$orderItemModel = new Application_Model_OrderItem();
$customerModel = new Application_Model_Customer('');
$productModel = new Application_Model_Product();
$orderTable = new Application_Model_DbTable_Orders();
$orderItemTable = new Application_Model_DbTable_OrderItems();
$customerTable = new Application_Model_DbTable_Cusomer();


$order_file       = '../data/orders.csv';
$order_items_file = '../data/orders_items.csv';
$producs_file     = '../data/prducts.csv';
$customers_file   = '../data/customers.csv';

if (fopen($order_file, 'r') !== false){
    while($data = fgetcsv($order_file, 1000, ',') !== false){
        list($id, $increment_id, $customer_id, $store_id, $date_created, $status, $qty, $price, $tax, $shipping_street,
            $shipping_country, $shipping_postcode, $shipping_price, $shipping_description, $grand_total, $currency, $payment_method) = $data;

        echo 'number of columns = '. count($data);
        printf("number of columns %d", count($data));

        var_dump($increment_id);
        var_dump($date_created);
        var_dump($qty);
        var_dump($price);
        var_dump($shipping_postcode);
        var_dump($grand_total);
        var_dump($payment_method);

        die;
    }
}

die;

$file = new SplFileObject($order_file);
$file->setFlags(SplFileObject::READ_CSV);
foreach ($file as $row) {
    var_dump($row);
    die;


}

die;
$file = new SplFileObject("animals.csv");
$file->setFlags(SplFileObject::READ_CSV);
foreach ($file as $row) {
    list($animal, $class, $legs) = $row;
    printf("A %s is a %s with %d legs\n", $animal, $class, $legs);
}


