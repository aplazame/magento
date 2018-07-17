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
            $this->_redirect('aplazame/payment/cart');
            return;
        }

        try {
            $this->getResponse()->setBody($this->getLayout()->createBlock('aplazame/payment_redirect')->toHtml());
        } catch (Aplazame_Sdk_Api_ApiClientException $e) {
            $session->addError('Aplazame Error: ' . $e->getMessage());
            $this->_redirect('aplazame/payment/cart');
            return;
        }

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
}
