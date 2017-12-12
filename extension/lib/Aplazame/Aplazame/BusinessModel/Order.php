<?php

/**
 * Order.
 */
class Aplazame_Aplazame_BusinessModel_Order
{
    public static function crateFromOrder(Mage_Sales_Model_Order $order)
    {
        $aOrder = new self();
        $aOrder->id = self::generateOrderIdFromShopId($order->getIncrementId());
        $aOrder->currency = $order->getOrderCurrencyCode();
        $aOrder->total_amount = Aplazame_Sdk_Serializer_Decimal::fromFloat($order->getGrandTotal());
        $aOrder->articles = array_map(array('Aplazame_Aplazame_BusinessModel_Article', 'crateFromOrderItem'), $order->getAllVisibleItems());

        if (($discounts = $order->getDiscountAmount()) !== null) {
            $aOrder->discount = Aplazame_Sdk_Serializer_Decimal::fromFloat(-$discounts);
        }

        return $aOrder;
    }

    public static function generateOrderIdFromShopId($shopId)
    {
        return sprintf('%s$$%d', $shopId, time());
    }

    public static function getShopIdFromOrderId($orderId)
    {
        $parts = explode('$$', $orderId);

        return $parts[0];
    }
}
