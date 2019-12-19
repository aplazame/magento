<?php

class Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder
{
    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public static function createFromOrder(Mage_Sales_Model_Order $order)
    {
        $status = $order->getState();

        switch ($status) {
            case 'canceled':
                $payment_status = 'cancelled';
                $status = 'cancelled';
                break;
            case 'closed':
                $payment_status = 'refunded';
                $status = 'refunded';
                break;
            case 'complete':
            case 'processing':
                $payment_status = 'payed';
                break;
            case 'holded':
            case 'new':
            case 'payment_review':
            case 'pending_payment':
                $payment_status = 'pending';
                $status = 'payment';
                break;
            default:
                $payment_status = 'unknown';
                $status = 'custom_' . $status;
        }

        try {
            $payment_method = $order->getPayment()->getMethodInstance()->getCode();
        } catch (Exception $e) {
            $payment_method = 'unknown';
        }

        $serialized = array(
            'customer' => Aplazame_Aplazame_BusinessModel_Customer::createFromOrder($order),
            'order' => Aplazame_Aplazame_BusinessModel_Order::crateFromOrder($order, $order->getCreatedAt()),
            'billing' => Aplazame_Aplazame_BusinessModel_Address::createFromAddress($order->getBillingAddress()),
            'meta' => Aplazame_Aplazame_BusinessModel_Meta::create(),
            'payment' => array(
                'method' => $payment_method,
                'status' => $payment_status,
            ),
            'status' => $status,
            'shipping' => Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromOrder($order),
        );

        return $serialized;
    }
}
