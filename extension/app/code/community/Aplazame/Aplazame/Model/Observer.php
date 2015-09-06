<?php


class Aplazame_Aplazame_Model_Observer extends Mage_Core_Model_Abstract
{
    protected function is_aplazame_payment($order)
    {
        $code = Aplazame_Aplazame_Model_Payment::METHOD_CODE;

        $parentOrder = Mage::getModel('sales/order')->loadByIncrementId(
            (int)$order->getIncrementId());

        return ($code == $parentOrder->getPayment()->getMethod());
    }

    /**
     * Method for updating the order status after completing a purchase
     */
    public function salesOrderPlaceAfter($observer)
    {
        $order = $observer->getOrder();

        if (!isset($order)) {
            return $this;
        }

        $payment = $order->getPayment();

        if (!isset($payment)) {
            return $this;
        }

        if (!$this->is_aplazame_payment($order)) {
            return $this;
        }

        $client = Mage::getModel('aplazame/api_client');
        $result = $client->setOrder($order)->updateOrder();

        return $this;
    }

    /**
     * Method after load order edition
     */
    public function salesOrderLoadAfter($observer)
    {
        return $this;
    }

    /**
     * Method used for canceling a Aplazame invoice when a Magento order is canceled
     */
    public function salesOrderPaymentCancel($observer)
    {
        $code = Aplazame_Aplazame_Model_Payment::METHOD_CODE;
        $order = $observer->getOrder();

        $orderId = explode("-", $order->getIncrementId());

        $nextOrder = Mage::getModel('sales/order')->loadByIncrementId(
            $orderId[0] . '-' . ((int)$orderId[1] + 1));

        if ($nextOrder->getId()) {
            return $this;
        }

        if (!$this->is_aplazame_payment($order)) {
            return $this;
        }

        $client = Mage::getModel('aplazame/api_client');
        $result = $client->setOrder($order)->cancelOrder();

        return $this;
    }
}
