<?php

class Aplazame_Aplazame_Helper_Payments extends Mage_Core_Helper_Abstract
{
    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     */
    public function authorizePayment($payment)
    {
        $order = $payment->getOrder();
        $mid = $order->getIncrementId();

        $payment->setTransactionId($mid);

        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(false);
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param bool $autoinvoice
     *
     * @return bool
     */
    public function isPaymentAccepted($payment, $autoinvoice)
    {
        if ($payment->getIsFraudDetected()) {
            return false;
        }

        if ((bool) $autoinvoice) {
            $payment->registerCaptureNotification($payment->getAmountAuthorized());
        }

        $order = $payment->getOrder();
        if (!$order->getEmailSent()) {
            $order->sendNewOrderEmail();
        }

        return true;
    }

    public function getAplazameRedirectUrl($type)
    {
        return Mage::getUrl('aplazame/payment/redirect', array(
            '_secure' => true,
            'type' => $type,
        ));
    }

    public function getAplazameCheckout($orderIncrementId, $type, $serializer = false)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        $checkout = Aplazame_Aplazame_BusinessModel_Checkout::createFromOrder($order, $type);
        if ($serializer) {
            return $checkout;
        }

        $response = $this->getApiClient()->create_checkout(Aplazame_Sdk_Serializer_JsonSerializer::serializeValue($checkout));
        return $response;
    }

    /**
     * @return Aplazame_Aplazame_Model_Api_Client
     */
    public function getApiClient()
    {
        return Mage::getModel('aplazame/api_client');
    }
}
