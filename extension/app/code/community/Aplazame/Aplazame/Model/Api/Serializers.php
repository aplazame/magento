<?php

require_once Mage::getBaseDir('lib').DS.'Aplazame'.DS.'Aplazame.php';


class Aplazame_Aplazame_Model_Api_Serializers extends Varien_Object
{
    private static function formatDecimals($price)
    {
        return Aplazame_Util::formatDecimals($price);
    }

    private static function _getMetadata()
    {
        return array(
            "module" => array(
                "name" => "aplazame:magento",
                "version" => Mage::getVersion()
            ),
            "version" => Mage::getConfig()->getModuleConfig('Aplazame_Aplazame')->version
        );
    }

    /**
     * @param Mage_Sales_Model_Order_Address $addr
     * @return array
     */
    protected function _getAddr($addr)
    {
        return array(
            "first_name"=> $addr->getFirstname(),
            "last_name"=> $addr->getLastname(),
            "phone"=> $addr->getTelephone(),
            "alt_phone"=> $addr->getAltTelephone(),
            "street" => $addr->getStreet(1),
            "address_addition" => $addr->getStreet(2),
            "city" => $addr->getCity(),
            "state" => $addr->getRegion(),
            "country" => $addr->getCountryModel()->getIso2Code(),
            "postcode" => $addr->getPostcode());
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function getCustomer($order)
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $logCustomer = Mage::getModel('log/customer')->load($customer->getId());
        $customer_serializer = array("gender"=>0);

        if ($customer->getId()) {
            $customer_serializer = array_merge($customer_serializer, array(
                "id"=>$customer->getId(),
                "type"=>"e",
                "email"=>$customer->getEmail(),
                "first_name"=>$customer->getFirstname(),
                "last_name"=>$customer->getLastname(),
                "date_joined"=>date(DATE_ISO8601, $customer->getCreatedAtTimestamp()),
                "last_login"=>date(DATE_ISO8601, $logCustomer->getLoginAtTimestamp())));
        } else {
            $customer_serializer = array_merge($customer_serializer, array(
                "type"=>"g",
                "email"=>$order->getCustomerEmail(),
                "first_name"=>$order->getCustomerFirstname(),
                "last_name"=>$order->getCustomerLastname()));
        }

        return $customer_serializer;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function getShipping($order)
    {
        $shipping = null;
        $shipping_address = $order->getShippingAddress();

        if ($shipping_address) {
            $shipping = array_merge($this->_getAddr($shipping_address), array(
                "price"=> self::formatDecimals($order->getShippingAmount()),
                "name"=>$order->getShippingMethod()
            ));

            if ($order->getShippingAmount() > 0) {
                $shipping["tax_rate"] = 100 * $order->getShippingTaxAmount() / $order->getShippingAmount();
            }
        }

        return $shipping;
    }

    private function getProductTaxRate($product)
    {
        $store = Mage::app()->getStore();
        $request = Mage::getSingleton('tax/calculation')->getRateRequest(null, null, null, $store);
        $taxclassid = $product->getData('tax_class_id');
        return Mage::getSingleton('tax/calculation')->getRate($request->setProductClassId($taxclassid));
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function getArticles($order)
    {
        $articles = array();

        /** @var Mage_Sales_Model_Order_Item $order_item */
        foreach ($order->getAllVisibleItems() as $order_item) {
            $productId = $order_item->getProductId();
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->load($productId);
            $discounts = $product->getPrice() - $product->getFinalPrice();

            $articles[] = array(
                "id" => $productId,
                "sku" => $order_item->getSku(),
                "name" => $order_item->getName(),
                "description" => substr($product->getDescription(), 0, 255),
                "url" =>$product->getProductUrl(),
                "image_url" => $product->getImageUrl(),
                "quantity" => intval($order_item->getQtyOrdered()),
                "price" => self::formatDecimals($order_item->getPrice() + $discounts),
                "tax_rate" => self::formatDecimals($this->getProductTaxRate($product)),
                "discount" => self::formatDecimals($discounts));
        }

        return $articles;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function getRenderOrder($order)
    {
        $discounts = $order->getDiscountAmount();

        if (is_null($discounts)) {
            $discounts = 0;
        }

        return array(
            "id"=>$order->getIncrementId(),
            "articles"=>$this->getArticles($order),
            "currency"=>$order->getOrderCurrencyCode(),
            "tax_amount"=>self::formatDecimals($order->getTaxAmount()),
            "total_amount"=>self::formatDecimals($order->getTotalDue()),
            "discount"=>-self::formatDecimals($discounts));
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getHistory($order)
    {
        /** @var Mage_Sales_Model_Order[] $history_collection */
        $history_collection = Mage::getModel('sales/order')
            ->getCollection()
            ->addAttributeToFilter('customer_id', array('like'=>$order->getCustomerId()));

        $history = array();
        foreach ($history_collection as $order_history) {
            $history[] = array(
                "id"=>$order_history->getIncrementId(),
                "amount"=>self::formatDecimals($order_history->getGrandTotal()),
                "due"=>self::formatDecimals($order_history->getTotalDue()),
                "status"=>$order_history->getStatus(),
                "type"=>$order_history->getPayment()->getMethodInstance()->getCode(),
                "order_date"=>date(DATE_ISO8601, strtotime($order_history->getCreatedAt())),
                "currency"=>$order_history->getOrderCurrencyCode(),
                "billing"=>$this->_getAddr($order_history->getBillingAddress()),
                "shipping"=>$this->getShipping($order_history));
        }

        return $history;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getOrderUpdate($order)
    {
        return array(
            "order"=>$this->getRenderOrder($order),
            "billing"=>$this->_getAddr($order->getBillingAddress()),
            "shipping"=>$this->getShipping($order),
            "meta"=>self::_getMetadata());
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getCheckout($order)
    {
        $info = $this->getInfoInstance();

        $merchant = array(
            "confirmation_url"=>Mage::getUrl("aplazame/payment/confirm", array('_secure'=>true)),
            "cancel_url"=>Mage::getUrl('aplazame/payment/cancel', array('_secure'=>true)) . '?status=error',
            "checkout_url"=>Mage::getUrl('aplazame/payment/cancel', array('_secure'=>true)),
            "success_url"=>Mage::getUrl('checkout/onepage/success', array('_secure'=>true)));

        return array(
            "toc"=>true,
            "merchant"=>$merchant,
            "customer"=>$this->getCustomer($order),
            "order"=>$this->getRenderOrder($order),
            "billing"=>$this->_getAddr($order->getBillingAddress()),
            "shipping"=>$this->getShipping($order),
            "meta"=>self::_getMetadata());
    }
}
