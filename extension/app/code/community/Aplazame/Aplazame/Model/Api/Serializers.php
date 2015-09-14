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

    private function _orderTotal()
    {
        return $this->getOrder()->getTotalDue();
    }

    public function getInvoiceFeeIncludeTax()
    {
        return $this->getTotal()->getAddress()->getInvoiceFee();
    }

    public function getInvoiceFeeExcludeTax()
    {
        return $this->getTotal()->getAddress()->getInvoiceFeeExcludedVat();
    }

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

    protected function getCustomer()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $logCustomer = Mage::getModel('log/customer')->loadByCustomer($customer);
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
            $order = $this->getOrder();

            $customer_serializer = array_merge($customer_serializer, array(
                "type"=>"g",
                "email"=>$order->getCustomerEmail(),
                "first_name"=>$order->getCustomerFirstname(),
                "last_name"=>$order->getCustomerLastname()));
        }

        return $customer_serializer;
    }

    protected function getShipping()
    {
        $order = $this->getOrder();
        $shipping = null;
        $shipping_address = $order->getShippingAddress();

        if ($shipping_address) {
            $shipping = array_merge($this->_getAddr($shipping_address), array(
                "price"=> static::formatDecimals($order->getShippingAmount()),
                "tax_rate"=>static::formatDecimals(100 * $order->getShippingTaxAmount() / $order->getShippingAmount()),
                "name"=>$order->getShippingMethod()
            ));
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

    protected function getArticles()
    {
        $order = $this->getOrder();
        $articles = array();
        $products = Mage::getModel('catalog/product');

        foreach ($order->getAllVisibleItems() as $order_item) {
            $productId = $order_item->getProductId();
            $product = $products->load($productId);
            $discounts = $product->getPrice() - $product->getFinalPrice();

            $articles[] = array(
                "id" => $order_item->getId(),
                "sku" => $order_item->getSku(),
                "name" => $order_item->getName(),
                "description" => substr($product->getDescription(), 0, 255),
                "url" =>$product->getProductUrl(),
                "image_url" => $product->getImageUrl(),
                "quantity" => intval($order_item->getQtyOrdered()),
                "price" => static::formatDecimals($order_item->getPrice() + $discounts),
                "tax_rate" => static::formatDecimals($this->getProductTaxRate($product)),
                "discount" => static::formatDecimals($discounts));
        }

        return $articles;
    }

    protected function getRenderOrder()
    {
        $order = $this->getOrder();
        $discounts = $order->getDiscountAmount();

        if (is_null($discounts)) {
            $discounts = 0;
        }

        return array(
            "id"=>$order->getIncrementId(),
            "articles"=>$this->getArticles(),
            "currency"=>$order->getOrderCurrencyCode(),
            "tax_amount"=>static::formatDecimals($order->getTaxAmount()),
            "total_amount"=>static::formatDecimals($this->_orderTotal()),
            "discount"=>-static::formatDecimals($discounts));
    }

    public function getHistory()
    {
        $order = $this->getOrder();
        $history_collection = Mage::getModel('sales/order')
            ->getCollection()
            ->addAttributeToFilter('customer_id', array('like'=>$order->getCustomerId()));

        $history = array();
        foreach ($history_collection as $order_history) {
            $history[] = array(
                "id"=>$order_history->getIncrementId(),
                "amount"=>static::formatDecimals($order_history->getGrandTotal()),
                "due"=>static::formatDecimals($order_history->getTotalDue()),
                "status"=>$order_history->getStatus(),
                "type"=>$order_history->getPayment()->getMethodInstance()->getCode(),
                "order_date"=>date(DATE_ISO8601, strtotime($order_history->getCreatedAt())),
                "currency"=>$order_history->getOrderCurrencyCode(),
                "billing"=>$this->_getAddr($order_history->getBillingAddress()),
                "shipping"=>$this->getShipping($order_history));
        }

        return $history;
    }

    public function getOrderUpdate()
    {
        $order = $this->getOrder();

        return array(
            "order"=>$this->getRenderOrder(),
            "billing"=>$this->_getAddr($order->getBillingAddress()),
            "shipping"=>$this->getShipping($order),
            "meta"=>static::_getMetadata());
    }

    public function getCheckout()
    {
        $order = $this->getOrder();
        $info = $this->getInfoInstance();

        $merchant = array(
            "confirmation_url"=>Mage::getUrl("aplazame/payment/confirm"),
            "cancel_url"=>Mage::getUrl('aplazame/payment/cancel') . '?status=error',
            "checkout_url"=>Mage::getUrl('aplazame/payment/cancel'),
            "success_url"=>Mage::getUrl('checkout/onepage/success'));

        return array(
            "toc"=>true,
            "merchant"=>$merchant,
            "customer"=>$this->getCustomer(),
            "order"=>$this->getRenderOrder(),
            "billing"=>$this->_getAddr($order->getBillingAddress()),
            "shipping"=>$this->getShipping($order),
            "meta"=>static::_getMetadata());
    }
}
