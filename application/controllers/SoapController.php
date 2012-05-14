<?php

class SoapController extends Zend_Controller_Action
{

    public $proxy = null;
    public $config;

    public $session_id = null;

    public function init()
    {
        $config = Zend_Registry::get('config');
        $this->proxy = new SoapClient($config->soap->client);
        $this->session_id = $this->proxy->login($config->soap->username, $config->soap->password);
    }

    public function indexAction()
    {
        $last_time ='';
        $filters = array('created_at' => array('gt' => $last_time),'status' => array('eq' => 'canceled'));


        //$orders = $this->proxy->call($this->session_id, 'sales_order.info', '100050027');

    }

    public function exportProductsAction()
    {
        $productModel = new Application_Model_Product();
        //'Sku, Name, Cost Price, Unit Price, Tax Class, Weight, Manufacturer, Created At, Updated At, In Stock,
        // Quantity in Stock, Primary Category, Primary Subcategory'
        $filters = array('sku' => array('eq' => 'HAC-4121035-03'));// '92574'));
        $prod = $this->proxy->call($this->session_id, 'catalog_product.list', array($filters));
        //$product = $this->proxy->call($this->session_id, 'catalog_product.info', '93074');
        $i = 0;

        foreach ($prod as $one) {

            if (strlen($one['product_id']) > 0){

                $one_arr = array();
                $productTable = new Application_Model_DbTable_Product();
                $product  = $this->proxy->call($this->session_id, 'catalog_product.info', $one['product_id']);
                $product = new Zend_Config($product);
                              var_dump($product);
                //$categories_ids = $product->categories;
                $all_childrens = $product->all_children;
                $all_childrens = explode(',', $all_childrens);

                $stock = $this->proxy->call($this->session_id, 'product_stock.list', $product->product_id);
                if (count($all_childrens) > 1){
                    foreach ($all_childrens as $cat) {
                        var_dump($cat);die;
                        $category = $this->proxy->call($this->session_id, 'catalog_category.info', $cat);
                        if ($category['level'] == 2){

                            //$second_level = $this->proxy->call($this->session_id, 'catalog_category.info', $all_childrens[2]);
                            //var_dump($second_level);die;

                        }
                        ///var_dump($category);die;

                        break;
                    }
                }


                //$attribute = $this->proxy->call($this->session_id, 'product_attribute_set.list');

                $row = $productTable->createRow();
                $row->product_id = $product->product_id;
                $row->sku        = $product->sku;
                $row->name       = $product->name;
                $row->cost_price = $product->cost;
                $row->unit_price = $product->price;
                $row->tax_class  = $product->tax_class_id;
                $row->weight     = ($product->weight)? $product->weight : 0;
                $row->manufacturer = $product->manufacturer;
                $row->created_at = $product->created_at;
                $row->updated_at = $product->updated_at;
                $row->in_stock   = ($stock[0]['is_in_stock'])? 'Y' : 'N';
                $row->qty_in_stock = (int)$stock[0]['qty'];
                $row->primary_category = '';
                $row->primary_subcategory = '';
                //$row->save();






                if ($i == 10)
                    break;
                $i++;
            }
        }

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