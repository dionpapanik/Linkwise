<?php

class DigitalUp_Linkwise_Block_Lnkws extends Mage_Core_Block_Template
{

    public function getLinkwiseData()
    {
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
                $params = '';
                foreach ($productCollection as $product) {
                    $params .= PHP_EOL .
                        'lw("addItem", {' . PHP_EOL .
                        'id: "' . $product->getSku() . '",' . PHP_EOL .
                        'price: "' . $this->_getLnkwsPrices($product) . '"' . PHP_EOL .
                        '});';
                }
                $params .= PHP_EOL . 'lw("listItems");';
                break;

            case 'catalog/product/view': // product page
                empty($params);
                $product = Mage::registry('current_product');
                $params = PHP_EOL .
                    'lw("addItem", {' . PHP_EOL .
                    'id: "' . $product->getSku() . '",' . PHP_EOL .
                    'price: "' . $this->_getLnkwsPrices($product) . '"' . PHP_EOL .
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
                            'price: "' . $this->_getLnkwsPrices($product) . '",' . PHP_EOL .
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

            case 'onestepcheckout/index/index': // lotusbreath onestepcheckout module!!
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
     * CAUTION if use getAllItems() needs to implemented differently!
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

    /**
     * return the prices for Linkwise without VAT.
     * @todo maybe needs to implemented in a clearer way!!
     *
     * @return string
     */
    private function _getLnkwsPrices($product)
    {
        // get the tax configuration from admin
        $helper = Mage::helper('linkwise');
        $tax_percentage = $helper->getTaxConfig();
        $tax = floatval(1 + ($tax_percentage / 100)); // transform 24 into 1.24 for calc purposes

        //calculate right prices
        $price = 0;
        $special_price = 0;
        $catalog_rule_price = 0;

        if (!$product->getHideSpecialPrice()) {
            $price = $product->getPrice();
            $special_price = $product->getSpecialPrice();
            $catalog_rule_price = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $product->getPrice());
            if ($special_price <= $catalog_rule_price && $special_price != 0) {
                $tmp = $special_price;
            } else {
                $tmp = $catalog_rule_price;
            }
            if ($tmp < $price && $tmp != 0) {
                $sale_price = number_format(($tmp), 2, '.', '');
            } else {
                $sale_price = number_format(($price), 2, '.', '');
            }
        } else {
            $sale_price = number_format(($price), 2, '.', '');
        }

        //return
        if ($tax_percentage == 0) {
            return $sale_price;
        }
        return round($sale_price / $tax, 2);
    }

}