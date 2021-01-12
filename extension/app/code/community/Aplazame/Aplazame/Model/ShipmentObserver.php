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

        /** @var Aplazame_Aplazame_Model_Payment $paymentMethod */
        $paymentMethod = $order->getPayment()->getMethodInstance();

        if (! ($paymentMethod instanceof Aplazame_Aplazame_Model_Payment)) {
            return $this;
        }

        /** @var Aplazame_Aplazame_Model_Api_Client $client */
        $client = Mage::getModel('aplazame/api_client');

        try {
            $payload = $client->getOrderCapture($order);
        } catch (Aplazame_Sdk_Api_ApiClientException $e) {
            throw $e;
        }

        if ($payload['remaining_capture_amount'] != 0) {
            try {
                $client->captureAmount($order, $payload['remaining_capture_amount']);
            } catch (Aplazame_Sdk_Api_ApiClientException $e) {
                throw $e;
            }
        }

        return $this;
    }
}
