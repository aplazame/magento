<?php

class Aplazame_Aplazame_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return Mage_Checkout_Model_Session
     */
    private function _getCheckoutSession()
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


    public function confirmAction()
    {
        $session = $this->_getCheckoutSession();
        $checkout_token = $this->getRequest()->getParam("order_id");

        if (!$checkout_token) {
            Mage::throwException($this->__('Confirm has no checkout token.'));
        }


        if ($session->getLastRealOrderId()) {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            $payment = $order->getPayment()->getMethodInstance();
            if (!$payment instanceof Aplazame_Aplazame_Model_Payment) {
                Mage::throwException($this->__('Unexpected payment method.'));
            }

            $payment->processConfirmOrder($order, $checkout_token);

            // TODO: add a boolean configuration option
            $order->sendNewOrderEmail();
            // $this->_redirect('checkout/onepage/success');
            return;
        }
        // $this->_redirectUrl(Mage::helper('checkout/url')->getCheckoutUrl());
    }

    public function cancelAction()
    {
        $session = $this->_getCheckoutSession();
        $orderId = $session->getLastRealOrderId();

        if ($orderId) {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            if ($order->getId() && $order->getState() === Mage_Sales_Model_Order::STATE_NEW) {
                Mage::helper('aplazame/cart')->resuscitateCartFromOrder($order, $this);
            }
        }

        $this->_redirectUrl(Mage::helper('checkout/url')->getCheckoutUrl());
    }
}
