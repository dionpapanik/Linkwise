<?php

class DigitalUp_Linkwise_Block_Success extends Mage_Core_Block_Template
{

    public function getSuccessData()
    {
        $params = array();
        empty($params);
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        $orderIncrementId = $order->getIncrementId();
        $items = $order->getAllItems();
        $counter = '1';
        foreach ($items as $product) {
            $params[0] .= PHP_EOL .
                'lw("addItem", {' . PHP_EOL .
                'id: "' . $product->getSku() . '",' . PHP_EOL .
                'price: "' . $this->_getLnkwsPrices($product) . '",' . PHP_EOL .
                'quantity: "' . (int)$product->getQtyOrdered() . '",' . PHP_EOL .
                'payout: "1"' . PHP_EOL .
                '});' . PHP_EOL;

            $params[1] = 'itemid[' . $counter . ']=' . $product->getSku() . '&amp;itemprice[' . $counter . ']=' . $this->_getLnkwsPrices($product) . '&amp;itemquantity[' . $counter . ']=' . (int)$product->getQtyOrdered() . '&amp;itempayout[' . $counter . ']=1';


            $counter++;
        }
        $params[0] .= 'lw("thankyou"),' . PHP_EOL .
            'orderid: "' . $orderIncrementId . '",' . PHP_EOL .
            'status: "pending" ' . PHP_EOL .
            '});' . PHP_EOL;

        return $params;
    }

    /*
     * @todo make it work properely
     * public function getSuccessData()
    {
        $params = array();
        empty($params);
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        $items = $order->getAllVisibleItems();
        $data = array();
        $count = 1;
        foreach ($items as $product) {
            $data[$count] = array(
                $dataPoints = array(
                    "id" => $product->getSku(),
                    "price" => $this->_getLnkwsPrices($product),
                    "quantity" => (int)$product->getQtyOrdered(),
                    "payout" => 1
                )
            );
            $count++;
        }

        return $data;
        // Zend_Debug::dump($data);
    }

    public function dataToJS()
    {
        $data = $this->getSuccessData();
        // Zend_Debug::dump($data);
        foreach ($data as $product => $value) {

            Zend_Debug::dump($value[1]);

        }
    }

    public function dataToURL($data)
    {

    }*/


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