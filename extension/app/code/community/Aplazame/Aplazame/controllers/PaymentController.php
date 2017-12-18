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
            if ($order->getId() && $order->getState() === Mage_Sales_Model_Order::STATE_NEW) {
                /** @var Aplazame_Aplazame_Helper_Cart $cart */
                $cart = Mage::helper('aplazame/cart');
                $cart->resuscitateCartFromOrder($order, $this);
            }
        }

        $this->_redirectUrl(Mage::helper('checkout/url')->getCheckoutUrl());
    }

    public function historyAction()
    {
        if (!$this->verifyAuthentication()) {
            Mage::throwException($this->__("You don't have permissions."));
        }

        $checkout_token = $this->getRequest()->getParam("checkout_token");
        if (!$checkout_token) {
            Mage::throwException($this->__('History has no checkout token.'));
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($checkout_token);
        if (!$order) {
            Mage::throwException($this->__('Order not found.'));
        }

        /** @var Mage_Sales_Model_Order[] $history_collection */
        $history_collection = Mage::getModel('sales/order')
            ->getCollection()
            ->addAttributeToFilter('customer_id', array('like'=> $order->getCustomerId()));

        $historyOrders = array_map(array('Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder', 'createFromOrder'), $history_collection);

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode(Aplazame_Sdk_Serializer_JsonSerializer::serializeValue($historyOrders)));
    }

    /**
     * @return bool
     */
    private function verifyAuthentication()
    {
        $privateKey = Mage::getStoreConfig('payment/aplazame/secret_api_key');

        $authorization = $this->getAuthorizationFromRequest();
        if (!$authorization || empty($privateKey)) {
            return false;
        }

        return ($authorization === $privateKey);
    }

    private function getAuthorizationFromRequest()
    {
        $token = $this->getRequest()->getParam('access_token');
        if ($token) {
            return $token;
        }

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            $headers = $this->getallheaders();
        }

        $headers = array_change_key_case($headers, CASE_LOWER);

        if (isset($headers['authorization'])) {
            return trim(str_replace('Bearer', '', $headers['authorization']));
        }

        return false;
    }

    private function getallheaders()
    {
        $headers = array();
        $copy_server = array(
            'CONTENT_TYPE'   => 'content-type',
            'CONTENT_LENGTH' => 'content-length',
            'CONTENT_MD5'    => 'content-md5',
        );

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $name = substr($name, 5);
                if (!isset($copy_server[$name]) || !isset($_SERVER[$name])) {
                    $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', $name)))] = $value;
                }
            } elseif (isset($copy_server[$name])) {
                $headers[$copy_server[$name]] = $value;
            }
        }

        if (!isset($headers['authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }

        return $headers;
    }
}
