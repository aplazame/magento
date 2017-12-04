<?php

class Aplazame_Aplazame_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    const METHOD_CODE = 'aplazame';

    protected $_code = self::METHOD_CODE;
    protected $_formBlockType = 'aplazame/payment_form';
    protected $_infoBlockType = 'aplazame/payment_info';
    protected $_canUseInternal         = false;
    protected $_canUseForMultishipping = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     *
     * @return $this
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        if ($payment->getIsFraudDetected()) {
            return $this;
        }

        $order = $payment->getOrder();
        $mid = $order->getIncrementId();

        $payment->setTransactionId($mid);

        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(false);

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return bool
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
        if ($payment->getIsFraudDetected()) {
            return false;
        }

        if ((bool) $this->getConfigData('autoinvoice')) {
            $payment->registerCaptureNotification($payment->getAmountAuthorized());
        }

        $order = $payment->getOrder();
        if (!$order->getEmailSent()) {
            $order->queueNewOrderEmail();
        }

        return true;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return bool
     */
    public function denyPayment(Mage_Payment_Model_Info $payment)
    {
        return true;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return $this
     */
    public function cancel(Varien_Object $payment)
    {
        $order = $payment->getOrder();

        /** @var Aplazame_Aplazame_Model_Api_Client $client */
        $client = Mage::getModel('aplazame/api_client');
        try {
            $client->cancelOrder($order);
        } catch (Aplazame_Sdk_Api_ApiClientException $e) {
            if ($e->getStatusCode() == 404) {
                return $this;
            }

            throw $e;
        }

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return $this
     */
    public function void(Varien_Object $payment)
    {
        return $this->cancel($payment);
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     *
     * @return $this
     */
    public function refund(Varien_Object $payment, $amount)
    {
        if (!((bool) $this->getConfigData('refund_method_magento_native'))) {
            return $this;
        }

        $order = $payment->getOrder();

        /** @var Aplazame_Aplazame_Model_Api_Client $client */
        $client = Mage::getModel('aplazame/api_client');
        try {
            $client->refundAmount($order, $amount);
        } catch (Aplazame_Sdk_Api_ApiClientException $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }


    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('aplazame/payment/redirect', array('_secure' => true));
    }

    public function getCheckoutSerializer()
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        return Aplazame_Aplazame_BusinessModel_Checkout::createFromOrder($order);
    }
}
