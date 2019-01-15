<?php

class Aplazame_Aplazame_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Intercept legacy offline credit memo requests for to rely the operation to Aplazame's platform.
     *
     * @deprecated since v1.0.0
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function salesOrderPaymentRefund($observer)
    {
        /** @var Mage_Sales_Model_Order_Creditmemo $creditMemo */
        $creditMemo = $observer->getCreditmemo();
        if (! $creditMemo->getOfflineRequested()) {
            // Online refunds are processed in native way without the need of this observer.
            return $this;
        }

        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $observer->getPayment();

        /** @var Aplazame_Aplazame_Model_Payment $paymentMethod */
        $paymentMethod = $payment->getMethodInstance();
        if (! ($paymentMethod instanceof Aplazame_Aplazame_Model_Payment)) {
            // Only return payments made with Aplazame
            return $this;
        }

        if ($paymentMethod->isMagentoNativeRefundMethodEnable()) {
            return $this;
        }

        $paymentMethod->refund($payment, $creditMemo->getBaseGrandTotal());

        return $this;
    }
}
