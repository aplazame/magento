<?php

class Aplazame_Aplazame_PaymentController extends Mage_Core_Controller_Front_Action
{
    private function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getAccessToken()
    {
        $request = new Zend_Controller_Request_Http();
        $authorization = $request->getHeader('authorization');

        if ($authorization) {
            $token = explode(' ', $authorization);
            $token =$token[1];
            if (isset($token) && is_string($token)) {
                return $token;
            }
            Mage::throwException($this->__('Authentication header format is invalid.'));
        }
        Mage::throwException($this->__('Authentication header is absent.'));
    }

    public function redirectAction()
    {
        $session = $this->_getCheckoutSession();

        if (!$session->getLastRealOrderId()) {
            $session->addError($this->__('Your order has expired.'));
            $this->_redirect('checkout/cart');
            return;
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
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
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            $order->getPayment()->getMethodInstance()->processConfirmOrder($order, $checkout_token);

            // TODO: add a boolean configuration option
            $order->sendNewOrderEmail();
            // $this->_redirect('checkout/onepage/success');
            return;
        }
        // $this->_redirect('checkout/onepage');
    }

    public function cancelAction()
    {
        $session = $this->_getCheckoutSession();
        $orderId = $session->getLastRealOrderId();

        if ($orderId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            if ($order->getId()) {
                Mage::helper('aplazame/cart')->resuscitateCartFromOrder($order);
            }
        }

        $this->_redirect('checkout/onepage');
    }

    public function historyAction()
    {
        $checkout_token = $this->getRequest()->getParam("checkout_token");

        if (!$checkout_token) {
            Mage::throwException($this->__('History has no checkout token.'));
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($checkout_token);
        $payment = $order->getPayment()->getMethodInstance();
        $code = Aplazame_Aplazame_Model_Payment::METHOD_CODE;

        if (!$payment or $code !== $payment->getCode()) {
            Mage::throwException($this->__('Order not found.'));
        }

        if ($this->_getAccessToken() !== $payment->getConfigData('secret_api_key')) {
            Mage::throwException($this->__('You don\'t have permissions.'));
        }

        $result = $payment->processHistory($order, $checkout_token);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($result);
    }
}
