<?php
/**
 * Created by JetBrains PhpStorm.
 * User: cdiacon
 * Date: 21/04/2012
 * Time: 10:49
 * To change this template use File | Settings | File Templates.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);
ini_set('memory_limit', -1);
// Start Magento app
define('MAGENTO', realpath(dirname(__FILE__)) . "/../../");
require_once MAGENTO . '/app/Mage.php';
//umask(0);
Mage::app();


$orderModel = Mage::getModel('sales/order');
$customerModel = Mage::getModel('customer/customer');
$customerAddress = Mage::getModel('customer/address');
$productModel = Mage::getModel('catalog/product');

$products = $productModel->getCollection()
    //->setPageSize(200)
;

$orders =  $orderModel->getCollection()
    //->setPageSize(10)
;
$customers = $customerModel->getCollection()
    //->addAttributeToFilter('entity_id', '47276')
    //->addAttributeToFilter('entity_id', '38775')
    //->setPageSize(100)
;



$time = time();


/**
 * export Products
 */

//exportProducts($products);


/**
 * export Customers
 */
//exportCustomers($customers);


/**
 * export Orders
 */
exportOrders($orders);

function exportProducts($products){
    $productModel = Mage::getModel('catalog/product');

    $i = 1;
    $fp = fopen('../../data/products.csv', 'w');
    fputs($fp, 'Sku, Name, Cost Price, Unit Price, Tax Class, Weight, Manufacturer, Created At, Updated At, In Stock, Quantity in Stock, Primary Category, Primary Subcategory' . PHP_EOL);

    foreach ($products as $product) {

        $productData = $productModel->load($product->getId());
        $categoryModel = Mage::getModel('catalog/category');
        $stockQty = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
        $manufacturer = $productModel->getAttributeText('manufacturer');
        $categories = $productData->getCategoryCollection()->getData();
        $main_category = $subcategory = null;
        foreach ($categories as $cat) {
            if ($cat['level'] == 2 && $main_category == null){
                $main_category = $categoryModel->load($cat['entity_id'])->getName();

            }
            if($cat['level'] == 4 && $subcategory == null){
                $subcategory = $categoryModel->load($cat['entity_id'])->getName();
            }
        }

        $ex = array();
        $ex[] = $productData->getSku();
        $ex[] = $productData->getName();
        $ex[] = number_format($productData->getCost(), 2);
        $ex[] = number_format($productData->getPrice(), 2);
        $ex[] = $productData->getTaxClassId();
        $ex[] = ($productData->getWeight())? $productData->getWeight() : '';
        $ex[] = ($manufacturer)? $manufacturer : '';
        $ex[] = $productData->getCreatedAt();
        $ex[] = $productData->getUpdatedAt();
        $ex[] = ($productData->getIsInStock())? 'Y' : 'N';
        $ex[] = (int)$stockQty;
        $ex[] = $main_category;
        $ex[] = $subcategory;


        fputcsv($fp, $ex);
        $i++;
    }
    fclose($fp);

}


function exportCustomers($customers){
    $i = 1;
    $customerAddress = Mage::getModel('customer/address');
    $customerGroup = Mage::getModel('customer/group');

    $fp = fopen('../../data/customers.csv', 'w');
    fputs($fp, 'Customer Id, Firstname, Lastname, Prefix, Created At, Updated At, Street, Country, Postcode, Email, Telephone, Wholesales/Retail, Subscribe newsletter' . PHP_EOL);
    foreach ($customers as $customer_arr) {
        $customer = Mage::getModel('customer/customer')->load($customer_arr->getId());
        $customerNewsletter =  Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail());
        $address = $customerAddress->load($customer->getId());
        $group_id = $customer->getGroupId();
        $group_name = $customerGroup->load($group_id)->getCode();
        $subscribed  = ($customerNewsletter->isSubscribed())? 'Yes' : 'No';
        $street = implode(',', $address->getStreet());

        $ex = array();
        $ex[] = $customer->getId();
        $ex[] = substr($customer->getFirstname(), 0, 14);
        $ex[] = substr($customer->getLastname(), 0, 14);
        $ex[] = $customer->getPrefix();
        $ex[] = $customer->getCreatedAt();
        $ex[] = $customer->getUpdatedAt();
        $ex[] = $street;
        $ex[] = $address->getCountry();
        $ex[] = $address->getPostcode();
        $ex[] = $customer->getEmail();
        $ex[] = $address->getTelephone();
        $ex[] = $group_name;
        $ex[] = $subscribed;

        fputcsv($fp, $ex);
        $i++;
    }
    fclose($fp);
}


function exportOrders($orders){
    $customerModel = Mage::getModel('customer/address');
    $time = microtime(true);
    $fp = fopen('../../data/orders.csv', 'w');
    fputs($fp, 'Increment Id, Customer Id,Store id, Date created, Status, Quantity, Price, Tax, Shipping Street, Shipping Country, Shipping Postcode, Shipping price, Shipping Description, Grand total, Currency, Payment method' . PHP_EOL);
    $fpp = fopen('../../data/order_items.csv', 'w');
    fputs($fpp, 'Order Id, SKU, Quantity, Unit price, Tax percent, Tax value, Discount amount, total price' . PHP_EOL);
    foreach ($orders as $order) {

        $payment = $order->getPayment()->getData('method');
        $customer = $customerModel->load($order->getCustomerId());
        $items = $order->getAllItems();

        $tax = $order->getTaxAmount();
        $shipping = $order->getShippingAmount();
        $grandTotal = $order->getGrandTotal();

        $ex = array();
        $ex[] = $order->getIncrementId();
        $ex[] = $order->getCustomerId();
        $ex[] = $order->getStoreId();
        $ex[] = $order->getCreatedAt();
        $ex[] = $order->getStatus();
        $ex[] = count($items);
        $ex[] = number_format($order->getSubtotal(),2);
        $ex[] = number_format($tax, 2);
        $ex[] = $customer->getData('street');
        $ex[] = $customer->getCountry();
        $ex[] = $customer->getPostcode();
        $ex[] = number_format($shipping, 2);
        $ex[] = $order->getShippingDescription();
        $ex[] = number_format($grandTotal, 2);
        $ex[] = $order->getOrderCurrencyCode();
        $ex[] = $payment;


        foreach ($items as $item) {
            $product_id = $item->getProductId();
            $product = Mage::getModel('catalog/product')->load($product_id);

            $exit = array();
            $exit[] = $order->getIncrementId();
            $exit[] = $product->getSku();
            $exit[] = (int)$item->getQtyOrdered();
            $exit[] = number_format($item->getPrice(), 2);
            $exit[] = number_format($item->getTaxPercent(), 2);
            $exit[] = number_format($item->getTaxAmount(), 2);
            $exit[] = number_format($item->getDiscountAmount(), 2);
            $exit[] = ($item->getPrice() * $item->getQtyOrdered()) + $item->getTaxAmount() - $item->getDiscountAmount();

            fputcsv($fpp, $exit);
        }
        fputcsv($fp, $ex);

    }
    fclose($fpp);
    fclose($fp);
    $duration = microtime(true) - $time;

    echo 'execution time: ' . round($duration / 60, 4) . ' min.';
}