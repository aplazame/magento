<?php

class Aplazame_Aplazame_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function redirectAction()
    {
        $session = $this->_getCheckoutSession();

        if (!$session->getLastRealOrderId()) {
            $session->addError($this->__('Your order has expired.'));
            $this->_redirect('checkout/cart');
            return;
        }

        $this->getResponse()->setBody($this->getLayout()->createBlock('aplazame/payment_redirect')->toHtml());

        $session->unsQuoteId();
        $session->unsRedirectUrl();
    }

    public function cancelAction()
    {
        $this->_redirectUrl(Mage::getUrl('aplazame/payment/cart'));
    }

    public function cartAction()
    {
        $session = $this->_getCheckoutSession();
        $orderId = $session->getLastRealOrderId();

        if ($orderId) {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            if ($order->getId()) {
                /** @var Aplazame_Aplazame_Helper_Cart $cart */
                $cart = Mage::helper('aplazame/cart');
                $cart->restoreCartFromOrder($order);
            }
        }

        $this->_redirectUrl(Mage::helper('checkout/url')->getCheckoutUrl());
    }

    public function historyAction()
    {
        if (!$this->verifyAuthentication()) {
            Mage::throwException($this->__("You don't have permissions."));
        }

        $checkoutToken = $this->getRequest()->getParam("checkout_token");
        if (!$checkoutToken) {
            Mage::throwException($this->__('History has no checkout token.'));
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($checkoutToken);
        if (!$order) {
            Mage::throwException($this->__('Order not found.'));
        }

        /** @var Mage_Sales_Model_Order[] $historyCollection */
        $historyCollection = Mage::getModel('sales/order')
            ->getCollection()
            ->addAttributeToFilter('customer_id', array('like'=> $order->getCustomerId()));

        $historyOrders = array_map(array('Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder', 'createFromOrder'), $historyCollection);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode(Aplazame_Sdk_Serializer_JsonSerializer::serializeValue($historyOrders)));
    }

    /**
     * @return bool
     */
    protected function verifyAuthentication()
    {
        $privateKey = Mage::getStoreConfig('payment/aplazame/secret_api_key');

        $authorization = $this->getAuthorizationFromRequest();
        if (!$authorization || empty($privateKey)) {
            return false;
        }

        return ($authorization === $privateKey);
    }

    protected function getAuthorizationFromRequest()
    {
        $token = $this->getRequest()->getParam('access_token');
        if ($token) {
            return $token;
        }

        return false;
    }
}
