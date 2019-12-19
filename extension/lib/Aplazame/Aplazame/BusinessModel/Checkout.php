<?php

/**
 * Checkout.
 */
class Aplazame_Aplazame_BusinessModel_Checkout
{
    public static function createFromOrder(Mage_Sales_Model_Order $order, $type)
    {
        $checkoutUrl = Mage::getUrl('aplazame/payment/cart');

        $merchant = new stdClass();
        $merchant->cancel_url = $checkoutUrl;
        $merchant->success_url = Mage::getUrl('checkout/onepage/success', array('_secure' => true));
        $merchant->pending_url = $merchant->success_url;
        $merchant->checkout_url = $checkoutUrl;
        $merchant->notification_url = Mage::getUrl(
            'aplazame/api/index',
            array(
                '_query' => array(
                    'path' => '/confirm/',
                ),
                '_nosid' => true,
                '_store' => Mage::app()->getDefaultStoreView(),
            )
        );
        $merchant->customer_history_url = Mage::getUrl(
            'aplazame/api/index',
            array(
                '_query' => array(
                    'path' => '/order/history/',
                ),
                '_nosid' => true,
                '_store' => Mage::app()->getDefaultStoreView(),
            )
        );

        $checkout = new self();
        $checkout->toc = true;
        $checkout->merchant = $merchant;
        $checkout->order = Aplazame_Aplazame_BusinessModel_Order::crateFromOrder($order);
        $checkout->customer = Aplazame_Aplazame_BusinessModel_Customer::createFromOrder($order);
        $checkout->billing = Aplazame_Aplazame_BusinessModel_Address::createFromAddress($order->getBillingAddress());
        $checkout->shipping = Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromOrder($order);
        $checkout->meta = Aplazame_Aplazame_BusinessModel_Meta::create();
        $checkout->product = array('type' => $type);

        return $checkout;
    }
}
