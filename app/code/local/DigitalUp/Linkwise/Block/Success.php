<?php

class DigitalUp_Linkwise_Block_Success extends Mage_Core_Block_Template
{
    public function getSuccessData()
    {
        $helper = Mage::helper('linkwise');

        // get data of the last order
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        $orderIncrementId = $order->getIncrementId();
        $items = $order->getAllVisibleItems();

        // empty
        $product_data = array();
        empty($product_data);

        // product counter for dataToURL
        $counter = 1;
        foreach ($items as $product) {
            $product_data[] = array(
                "orderIncrementId" => $orderIncrementId,
                "product_counter" => $counter,
                "id" => $product->getSku(),
//                "price" => $this->_getLnkwsPrices($product),
                "price" => $helper->getOrderLnkwsPrices($product),
                "quantity" => (int)$product->getQtyOrdered(),
                "payout" => 1
            );
            $counter++;
        }
        return $product_data;
    }

    public function dataToJS()
    {
        $product_data = $this->getSuccessData();
        $data_to_js = null;
        foreach ($product_data as $data) {
            $data_to_js .= PHP_EOL .
                'lw("addItem", {' . PHP_EOL .
                'id: "' . $data["id"] . '",' . PHP_EOL .
                'price: "' . $data["price"] . '",' . PHP_EOL .
                'quantity: "' . $data["quantity"] . '",' . PHP_EOL .
                'payout: "' . $data["payout"] . '"' . PHP_EOL .
                '});' . PHP_EOL;
        }

        $data_to_js .= 'lw("thankyou"),' . PHP_EOL .
            'orderid: "' . $data["orderIncrementId"] . '",' . PHP_EOL .
            'status: "pending" ' . PHP_EOL .
            '});' . PHP_EOL;

        return $data_to_js;

    }

    public function dataToURL()
    {
        $product_data = $this->getSuccessData();
        $data_to_url = null;
        foreach ($product_data as $data) {
            $counter = $data["product_counter"];
            $data_to_url .= 'itemid[' . $counter . ']=' . $data["id"] . '&amp;itemprice[' . $counter . ']=' . $data["price"] . '&amp;itemquantity[' . $counter . ']=' . $data["quantity"] . '&amp;itempayout[' . $counter . ']=' . $data["payout"] . '&amp;';
        }

        $data_to_url .= 'status=pending&amp;orderid=' . $data["orderIncrementId"];
        return $data_to_url;
    }
}