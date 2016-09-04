<?php

class DigitalUp_Linkwise_Block_Lnkws extends Mage_Core_Block_Template
{

    public function getLinkwiseData()
    {

        $inclTax = false;

        $type = $this->getCurrentPageType();
        switch ($type) {
            case 'cms/index/index': //home page
                empty($params);

                $params = 'lw("viewhome");';
                break;

            case 'catalog/category/view': //category page
                $category = Mage::registry('current_category');
                $productCollection = Mage::getResourceModel('catalog/product_collection')
                    ->addCategoryFilter($category)
                    ->addAttributeToFilter('status', 1)
                    ->addAttributeToFilter('visibility', array('in' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH))
                    ->addAttributeToSelect(array('sku', 'price', 'special_price'));

                empty($params);
                foreach ($productCollection as $product) {
                    $totalvalue = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), $inclTax);
                    $params .= 'lw("addItem", {' . PHP_EOL .
                        'id: "' . $product->getSku() . '",' . PHP_EOL .
                        'price: "' . $totalvalue . '"' . PHP_EOL .
                        '});';
                }
                $params .= 'lw("listItems");';

                Zend_Debug::dump($params);
                break;

            case 'catalog/product/view': // product page
                echo "product";
                empty($params);

                $product = Mage::registry('current_product');
                $sku = $product->getSku();
                $totalvalue = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), $inclTax);

                Zend_Debug::dump($sku);
                Zend_Debug::dump($totalvalue);

                $params = 'lw("addItem", {' . PHP_EOL .
                    'id: "' . $product->getSku() . '",' . PHP_EOL .
                    'price: "' . $totalvalue . '"' . PHP_EOL .
                    '});' . PHP_EOL .
                    'lw("viewItem");';

                Zend_Debug::dump($params);
                break;

            case 'checkout/cart/index': // cart page

                empty($params);
                $cart = Mage::getSingleton('checkout/session')->getQuote();
                $items = $cart->getAllVisibleItems();
                if (count($items) > 0) {
                    foreach ($items as $item) {

                        $product = $item->getProduct();
                        $totalvalue = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), $inclTax);

                        $params .= 'lw("addItem", {' . PHP_EOL .
                            'id: "' . $product->getSku() . '",' . PHP_EOL .
                            'price: "' . $totalvalue . '",' . PHP_EOL .
                            'quantity: "' . (int)$item->getQty() . '"' . PHP_EOL .
                            '});'. PHP_EOL;
                    }
                    $params .= 'lw("viewCart");';
                }
                // Zend_Debug::dump($params);

                unset($cart, $items, $item, $product);
                break;
        }

        return $params;
    }

    /**
     * Checks the current module, controller, action to identify the page
     *
     * @return string
     */
    public function getCurrentPageType()
    {

        $module = Mage::app()->getRequest()->getModuleName();
        $controller = Mage::app()->getRequest()->getControllerName();
        $action = Mage::app()->getRequest()->getActionName();

        $page_type = $module . '/' . $controller . '/' . $action;

        return $page_type;

        Zend_Debug::dump($page_type);
    }


    /**
     * @todo
     *
     * @return string
     */
    private function _getProductSku($product)
    {

        // todo get the right data

    }

}