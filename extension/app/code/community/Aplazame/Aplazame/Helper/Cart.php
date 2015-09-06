<?php

class Aplazame_Aplazame_Helper_Cart extends Mage_Core_Helper_Abstract
{
    /**
     * Funcion que resucita un carrito (quote) en caso
     * de producirse algún fallo en el proceso de cobro de aplazame
     * o bien se ha rechazado la operación.
     */
    public function resuscitateCartFromOrder(Mage_Sales_Model_Order $order)
    {
        $this
            ->_cancelOrder($order)
            ->_resuscitateCartItems($order)
            ->_setCheckoutInfoFromOldOrder($order);

        return $this;
    }

    /**
     * Cancela la order anterior que se le pasa como parametro
     *
     * @param Mage_Sales_Model_Order $order
     * @return $this
     */
    protected function _cancelOrder(Mage_Sales_Model_Order $order)
    {
        $order
            ->cancel()
            ->save();

        return $this;
    }


    /**
     * Re-añade los productos comprados a carrito nuevamente
     *
     * @param Mage_Sales_Model_Order $order
     * @return $this
     */
    protected function _resuscitateCartItems(Mage_Sales_Model_Order $order)
    {
        foreach ($order->getItemsCollection() as $orderItem) {
            $this->getCart()->addOrderItem($orderItem);
        }

        $this->getCart()->save();

        return $this;
    }

    /**
     * Coge las billing y shipping address de la ultima order
     * el checkout method y shipping method
     * y las vuelve a meter en quote actual para que el checkout
     * pueda tener las direcciones pre-populadas.
     *
     * @param Mage_Sales_Model_Order $order
     */
    protected function _setCheckoutInfoFromOldOrder(Mage_Sales_Model_Order $order)
    {
        $checkoutSession = $this->getCheckoutSession();
        $quote = $checkoutSession->getQuote();
        $oldQuote = Mage::getModel('sales/quote')->load($order->getQuoteId());

        $quote->setCheckoutMethod($oldQuote->getCheckoutMethod());

        if ($oldQuote->getId()) {
            $billingAddress = clone $oldQuote->getBillingAddress();
            $billingAddress->setQuote($quote);

            $shippingAddress = clone $oldQuote->getShippingAddress();
            $shippingAddress->setQuote($quote);

            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->removeAddress($quote->getShippingAddress()->getId());

            $quote->setBillingAddress($billingAddress);
            $quote->setShippingAddress($shippingAddress);
        }

        $quote->save();

        return $this;
    }

    /**
     * @return Mage_Checkout_Model_Cart
     */
    public function getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }
}
