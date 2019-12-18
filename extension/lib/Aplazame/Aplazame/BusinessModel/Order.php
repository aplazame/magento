<?php

/**
 * Order.
 */
class Aplazame_Aplazame_BusinessModel_Order
{
    public static function crateFromOrder(Mage_Sales_Model_Order $order, $order_date = null)
    {
        $aOrder = new self();
        $aOrder->id = $order->getIncrementId();
        $aOrder->currency = $order->getOrderCurrencyCode();
        $aOrder->total_amount = Aplazame_Sdk_Serializer_Decimal::fromFloat($order->getGrandTotal());
        $aOrder->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat(100 * $order->getTaxAmount() / ($order->getGrandTotal() - $order->getTaxAmount()));
        $aOrder->articles = array_map(array('Aplazame_Aplazame_BusinessModel_Article', 'crateFromOrderItem'), $order->getAllVisibleItems());

        if (($discounts = $order->getDiscountAmount()) !== null) {
            $aOrder->discount = Aplazame_Sdk_Serializer_Decimal::fromFloat(-$discounts);
        }

        if ($order_date) {
            $aOrder->created = Aplazame_Sdk_Serializer_Date::fromDateTime(new DateTime($order_date));
        }

        return $aOrder;
    }
}
