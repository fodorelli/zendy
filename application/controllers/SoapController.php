<?php

class SoapController extends Zend_Controller_Action
{

    public $soap = null;
    public $config;

    public $session_id = null;

    public function init()
    {
        $config = Zend_Registry::get('config');  var_dump($config->soap->client);die;
        $proxy = new SoapClient($config->soap->client);
        $this->session_id = $proxy->login($config->soap->username, $config->soap->password);
    }

    public function indexAction()
    {

        $orders = $this->soap->call($this->session_id, 'sales/order.info', '100061148');

        var_dump($orders);

        //$orderModel = new Application_Model_DbTable_Orders();
        //var_dump($orderModel->fetchAll());
    }

    public function exportProductsAction()
    {
        // action body
    }

    public function exportOrdersAction()
    {
        // action body
    }

    public function exportCustomersAction()
    {
        // action body
    }

}