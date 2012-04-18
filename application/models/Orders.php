<?php

class Application_Model_Orders
{
    public $orders = array();

    public $total;
    public $totalSum;




    public function getOrderByDate($start , $end = null)
    {
        $soap = new SoapClient('http://alexandalexa.dev/api/soap/?wsdl');
        $session_id = $soap->login('alexa-api', 'goaway');

        if ($end == null){
            $date = new Zend_Date();
            $date = $date->toString(Zend_Date::DATETIME_SHORT);

            //$end = '1/17/12 3:02 PM';
        }
        $filters  = array('increment_id' => array('gt' => '100050027'));


        $result = $soap->call($session_id, 'sales_order.list', array($filters));

        echo '</br>' . count($result);
        //var_dump($result);die;

         return $result;

    }

    public function __toString()
    {
        var_dump($this->orders);
    }

}