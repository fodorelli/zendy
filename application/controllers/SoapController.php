<?php

class SoapController extends Zend_Controller_Action
{

    public $proxy = null;
    public $config;

    public $session_id = null;
    /*
     * attribute options for manufacturer
     */
    public $attribute_options = array();

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
        $filters = array('product_id' => array('from' => '10', 'to' => '500'));
        $prod = $this->proxy->call($this->session_id, 'catalog_product.list', array($filters));
        //$product = $this->proxy->call($this->session_id, 'catalog_product.info', '93074');
        $this->attribute_options = $this->proxy->call($this->session_id, 'product_attribute.options', array('attribute_id'=>'66'));


        foreach ($prod as $one) {

            if (strlen($one['product_id']) > 0){

                $one_arr = array();
                $productTable = new Application_Model_DbTable_Product();
                $product  = $this->proxy->call($this->session_id, 'catalog_product.info', $one['product_id']);
                $product = new Zend_Config($product);

                $categories_ids = $product->categories;

                if (count($categories_ids) > 1){
                    $cats = $this->loadCategory($categories_ids);
                }
                $stock = $this->proxy->call($this->session_id, 'product_stock.list', $product->product_id);

                $newRecord = $productTable->createRow();

                $where = $productTable->select()->where('product_id =?', $product->product_id);
                $record = $productTable->fetchRow($where);
                if ($record != null){

                    $row =  $record;
                }else{
                    $row = $newRecord;
                    $row->product_id = $product->product_id;
                    $row->sku        = $product->sku;
                }
                $row->name       = $product->name;
                $row->cost_price = $product->cost;
                $row->unit_price = $product->price;
                $row->tax_class  = $product->tax_class_id;
                $row->weight     = ($product->weight)? $product->weight : 0;
                $row->manufacturer = $this->getValueById($product->manufacturer);
                $row->created_at = $product->created_at;
                $row->updated_at = $product->updated_at;
                $row->in_stock   = ($stock[0]['is_in_stock'])? 'Y' : 'N';
                $row->qty_in_stock = (int)$stock[0]['qty'];
                $row->primary_category = (isset($cats['primary_category'])? $cats['primary_category'] : '');
                $row->primary_subcategory = utf8_decode(isset($cats['primary_subcategory'])? $cats['primary_subcategory'] : '');
                $row->save();

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
    /**
     * load category by given array of ids
     * @param $category_ids
     * @return array
     */
    private function loadCategory($category_ids){

        $cats = array();
        if(count($category_ids) > 1){

            foreach ($category_ids as $cat_id) {

                if (isset($cats['primary_category']) && isset($cats['primary_subcategory']))
                    break;
                $category = $this->proxy->call($this->session_id, 'catalog_category.info', $cat_id);
                if ($category['level'] ==2){

                   $cats['primary_category'] = $category['name'];
                }

                if ($category['level'] == 4 && trim($category['name']) != 'View All')
                    $cats['primary_subcategory'] = $category['name'];

            }

        }
        return $cats;
    }
    /*
     * get attribute value by id
     * @id string
     */
    private function getValueById($id)
    {
        foreach ($this->attribute_options as $attribute) {

            if($attribute['value'] == $id)
                return $attribute['label'];

        }
        return '';


    }

}