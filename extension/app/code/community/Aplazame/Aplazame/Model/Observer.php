<?php


class Aplazame_Aplazame_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function is_aplazame_payment($order)
    {
        $code = Aplazame_Aplazame_Model_Payment::METHOD_CODE;

        /** @var Mage_Sales_Model_Order $parentOrder */
        $parentOrder = Mage::getModel('sales/order')->loadByIncrementId(
            (int)$order->getIncrementId());

        return ($code == $parentOrder->getPayment()->getMethod());
    }

    /**
     * Method for updating the order status after completing a purchase
     */
    public function salesOrderPlaceAfter($observer)
    {
        /** @var Mage_Sales_Model_Order|null $order */
        $order = $observer->getOrder();

        if (!isset($order)) {
            return $this;
        }

        $payment = $order->getPayment();
        if (!$payment) {
            return $this;
        }

        if (!$this->is_aplazame_payment($order)) {
            return $this;
        }

        /** @var Aplazame_Aplazame_Model_Api_Client $client */
        $client = Mage::getModel('aplazame/api_client');
        $client->updateOrder($order);

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
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderPaymentCancel($observer)
    {
        /** @var Mage_Sales_Model_Order|null $order */
        $order = $observer->getOrder();

        $orderId = explode("-", $order->getIncrementId());
        if (isset($orderId[1])) {
            /** @var Mage_Sales_Model_Order $nextOrder */
            $nextOrder = Mage::getModel('sales/order')->loadByIncrementId(
                $orderId[0] . '-' . ((int)$orderId[1] + 1)
            );

            if ($nextOrder->getId()) {
                return $this;
            }
        }

        if (!$this->is_aplazame_payment($order)) {
            return $this;
        }

        /** @var Aplazame_Aplazame_Model_Api_Client $client */
        $client = Mage::getModel('aplazame/api_client');
        try {
            $client->cancelOrder($order);
        } catch (Mage_Core_Exception $e) {
            if ($e->getMessage() === 'Aplazame error code 404: Not found') {
                return $this;
            }

            throw $e;
        }

        return $this;
    }

    /**
     * Method to send a partial (refund) or total (cancel) refund to aplazame when a creditmemo is created
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function salesOrderPaymentRefund($observer)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $observer->getPayment();

        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = $observer->getCreditmemo();

        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();

        if (!$this->is_aplazame_payment($order)) {
            return $this;
        }

        $remainingAmountAfterRefund = $order->getBaseGrandTotal() - $order->getBaseTotalRefunded();
        $refundedTotal = $creditmemo->getBaseGrandTotal();

        /** @var Aplazame_Aplazame_Model_Api_Client $client */
        $client = Mage::getModel('aplazame/api_client');
        if($remainingAmountAfterRefund == 0)
        {
            //total is refunded so we cancel order at aplazame side
            $client->cancelOrder($order);
        } else {
            //partial refund so we refund at aplazame side
            $client->refundAmount($order, $refundedTotal);
        }

        return $this;
    }
}
