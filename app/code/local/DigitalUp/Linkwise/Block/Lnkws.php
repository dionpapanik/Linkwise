<?php

class DigitalUp_Linkwise_Block_Lnkws extends Mage_Core_Block_Template
{

	/**
+     * identify the curent page based on the m/c/a
	  * bulid the js
	  *
+     * @return $params (js string)
+     */

    public function getLinkwiseData()
    {
        $helper = Mage::helper('linkwise');
        $type = $this->getCurrentPageType();
        switch ($type) {
            case 'cms/index/index': //home page
                empty($params);
                $params = 'lw("viewhome");';
                break;

            case 'catalog/category/view': //category page

			    /**
+   		  	* return products not for whole category
+				* but for the exact page user is
+     			*/

           		 // Varien_Profiler::start('catalog/category/view');

                $currentPage = (int)Mage::App()->getRequest()->getParam('p'); // read the pagination of the user
                $currentPage = (($currentPage == 0) ? 1 : $currentPage);

                $limit = (int)Mage::App()->getRequest()->getParam('limit'); // read the limit of the user
                $limit = (($limit == 0) ? 12 : $limit);

                $dir = (string)Mage::App()->getRequest()->getParam('dir'); // read the direction of the user
                $dir = (($dir == 'asc') ? 'asc' : 'desc');

                $order = (string)Mage::App()->getRequest()->getParam('order'); // read the ordering of the user //position, price etc
                $order = (($order == 0) ? 'position' : $order);

                $category = Mage::registry('current_category');
                $productCollection = Mage::getResourceModel('catalog/product_collection')
                    ->addCategoryFilter($category)
                    ->addAttributeToFilter('status', 1)
                    ->addAttributeToFilter('visibility', array('in' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH))
                    ->addAttributeToSelect(array('sku', 'price', 'special_price', 'tax_class_id'))
                    ->addAttributeToSort($order, $dir)
                    ->setCurPage($currentPage)
                    ->setPageSize($limit);

                $productCollection->load();

                empty($params);
                $params = '';
                foreach ($productCollection as $product) {
                    $params .= PHP_EOL .
                        'lw("addItem", {' . PHP_EOL .
                        'id: "' . $product->getSku() . '",' . PHP_EOL .
                        'price: "' . $helper->getCatalogLnkwsPrices($product) . '"' . PHP_EOL .
                        '});';
                }
                $params .= PHP_EOL . 'lw("listItems");';

                // Varien_Profiler::stop('catalog/category/view');
                break;

            case 'catalog/product/view': // product page
                empty($params);
                $product = Mage::registry('current_product');
                $params = PHP_EOL .
                    'lw("addItem", {' . PHP_EOL .
                    'id: "' . $product->getSku() . '",' . PHP_EOL .
                    'price: "' . $helper->getCatalogLnkwsPrices($product) . '"' . PHP_EOL .
                    '});' . PHP_EOL .
                    'lw("viewItem");';

                break;

            case 'checkout/cart/index': // cart page
                empty($params);
                $params = '';
                $cart = Mage::getSingleton('checkout/session')->getQuote();
                $items = $cart->getAllVisibleItems();
                if (count($items) > 0) {
                    foreach ($items as $item) {
                        $product = $item->getProduct();
                        $params .= PHP_EOL .
                            'lw("addItem", {' . PHP_EOL .
                            'id: "' . $this->_getProductSku($product) . '",' . PHP_EOL .
                            'price: "' . $helper->getCatalogLnkwsPrices($product) . '",' . PHP_EOL .
                            'quantity: "' . (int)$item->getQty() . '"' . PHP_EOL .
                            '});' . PHP_EOL;
                    }
                    $params .= 'lw("viewCart");';
                }

                unset($cart, $items, $item, $product);
                break;

            case 'checkout/onepage/index': // checkout page
                empty($params);
                $params = 'lw("checkout");';
                break;

            case 'onestepcheckout/index/index': // onestepcheckout module!!
                empty($params);
                $params = 'lw("checkout");';
                break;
        }

        return $params;
    }

    /**
     * Checks the current module/controller/action to identify the page
     *
     * @return string
     */
    public function getCurrentPageType()
    {

        $module = Mage::app()->getRequest()->getModuleName();
        $controller = Mage::app()->getRequest()->getControllerName();
        $action = Mage::app()->getRequest()->getActionName();

        $page_type = $module . '/' . $controller . '/' . $action;

        return (string)$page_type;

        // Zend_Debug::dump($page_type);
    }


    /**
     * used only in cart case
     * returns the sku of the simple and the sku of the config
     * works only for the getAllVisibleItems() function
     * CAUTION if use getAllItems() needs to implemented diferently!
     *
     * @todo implement it for rest types
     *
     * @return string
     */
    private function _getProductSku($product)
    {
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $id = Mage::getModel('catalog/product')->load($product->getId())->getSku();
        } elseif ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            $id = $product->getSku();
        }
        return $id;
    }
}