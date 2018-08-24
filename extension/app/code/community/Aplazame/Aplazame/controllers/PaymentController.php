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

        if (! $session->getLastRealOrderId()) {
            $session->addError($this->__('Your order has expired.'));
            $this->_redirect('aplazame/payment/cart');

            return;
        }

        try {
            $this->getResponse()
                 ->setBody($this->getLayout()
                                ->createBlock('aplazame/payment_redirect')
                                ->toHtml())
            ;
        } catch (Aplazame_Sdk_Api_ApiClientException $e) {
            $session->addError('Aplazame Error: ' . $e->getMessage());
            $this->_redirect('aplazame/payment/cart');

            return;
        }

        $session->unsQuoteId();
        $session->unsRedirectUrl();
    }

    public function cartAction()
    {
        $session = $this->_getCheckoutSession();
        $orderId = $session->getLastRealOrderId();
        if (! $orderId) {
            $this->goToCheckout();
            return;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')
                     ->loadByIncrementId($orderId)
        ;
        if (! $order->getId()) {
            $this->goToCheckout();
            return;
        }

        /** @var Aplazame_Aplazame_Helper_Cart $cartHelper */
        $cartHelper = Mage::helper('aplazame/cart');

        /** @var Aplazame_Aplazame_Model_Api_Client $client */
        $client = Mage::getModel('aplazame/api_client');
        $aOrder = $client->fetch($order->getIncrementId());
        if (! $aOrder) {
            $cartHelper->restoreCartFromOrder($order);
            $this->goToCheckout();
            return;
        }

        switch ($aOrder['status']) {
            case 'ok':
                $this->goToSuccess();
                return;
            case 'pending':
                switch ($aOrder['status_reason']) {
                    case 'in_process':
	                    $cartHelper->cancelOrder($order);
	                    $cartHelper->restoreCartFromOrder($order);
                        $this->goToCheckout();
                        return;
                    default:
                        $this->goToSuccess();
                        return;
                }
                // no break
            case 'ko':
                $cartHelper->restoreCartFromOrder($order);
                $this->goToCheckout();
                return;
        }

        $this->goToCheckout();
    }

    private function goToCheckout()
    {
        $this->_redirectUrl(Mage::helper('checkout/url')
                                ->getCartUrl());
    }

    private function goToSuccess()
    {
        $this->_redirectUrl(Mage::getUrl('checkout/onepage/success', array('_secure' => true)));
    }
}
