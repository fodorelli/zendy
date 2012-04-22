<?php

class SoapController extends Zend_Controller_Action
{

    public $soap = null;

    public $session_id = null;

    public function init()
    {

        $this->soap = new SoapClient('http://aatest.alexandalexa.com/api/soap/?wsdl');
        $this->session_id = $this->soap->login('logicspot', 'l0gicspot');
    }

    public function indexAction()
    {
        $orderModel = new Application_Model_DbTable_Orders();
        var_dump($orderModel->fetchAll());
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