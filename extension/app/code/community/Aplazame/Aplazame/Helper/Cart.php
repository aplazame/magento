<?php

class Aplazame_Aplazame_Helper_Cart extends Mage_Core_Helper_Abstract
{
    public function restoreCartFromOrder(Mage_Sales_Model_Order $order)
    {
        $this->cancelOrder($order);
        $this->reactivateQuote($this->getQuote($order->getQuoteId()));
    }

    public function approveOrder(Mage_Sales_Model_Order $order)
    {
        $order->getPayment()->accept();
        $order->save();
    }

    public function cancelOrder(Mage_Sales_Model_Order $order)
    {
        $payment = $order->getPayment();

        $payment->deny();

        /** @var Aplazame_Aplazame_Model_Payment $aplazame */
        $aplazame= Mage::getModel('aplazame/payment');
        try {
            $aplazame->cancel($payment);
        } catch (Exception $e) {
            // do nothing
        }

        $order->save();
    }

    private function reactivateQuote(Mage_Sales_Model_Quote $quote)
    {
        if (!$quote->getId()) {
            return;
        }

        $quote->setIsActive(1)
              ->setReservedOrderId(null)
              ->save()
        ;
        $this->getCheckoutSession()
             ->replaceQuote($quote)
             ->unsLastRealOrderId()
        ;
    }

    /**
     * @param int $quoteId Quote identifier
     * @return Mage_Sales_Model_Quote
     */
    private function getQuote($quoteId)
    {
        return Mage::getModel('sales/quote')->load($quoteId);
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    private function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }
}
