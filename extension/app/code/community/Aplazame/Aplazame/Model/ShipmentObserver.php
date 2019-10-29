<?php

class Aplazame_Aplazame_Model_ShipmentObserver extends Mage_Core_Model_Abstract
{
    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     *
     * @throws Mage_Core_Exception
     */
    public function captureOrder($observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getShipment()->getOrder();

        /** @var Aplazame_Aplazame_Model_Payment|Aplazame_Aplazame_Model_PaymentPayLater $paymentMethod */
        $paymentMethod = $order->getPayment()->getMethodInstance();

        if (! ($paymentMethod instanceof Aplazame_Aplazame_Model_PaymentPayLater)) {
            // Only capture payments made with Aplazame Pay Later
            return $this;
        }

        $amount = $order->getGrandTotal() - $order->getTotalRefunded();

        /** @var Aplazame_Aplazame_Model_Api_Client $client */
        $client = Mage::getModel('aplazame/api_client');
        try {
            $client->captureAmount($order, $amount);
        } catch (Aplazame_Sdk_Api_ApiClientException $e) {
            throw $e;
        }

        return $this;
    }
}
