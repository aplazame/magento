<?php

final class Aplazame_Aplazame_Api_Order
{
    /** @var Mage_Sales_Model_Order */
    private $orderModel;

    public function __construct(Mage_Sales_Model_Order $orderModel)
    {
        $this->orderModel = $orderModel;
    }

    public function history(array $params)
    {
        if (!isset($params['order_id'])) {
            return Aplazame_Aplazame_ApiController::not_found();
        }

        $order = $this->orderModel->loadByIncrementId($params['order_id']);
        if (!$order->getId()) {
            return Aplazame_Aplazame_ApiController::not_found();
        }

        /** @var Mage_Sales_Model_Resource_Order_Collection $orders */
        $orders = $this->orderModel
            ->getCollection()
            ->addAttributeToFilter('customer_id', array('like' => $order->getCustomerId()))
        ;

        $historyOrders = array();

        foreach ($orders->getItems() as $order) {
            $historyOrders[] = Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder::createFromOrder($order);
        }

        return Aplazame_Aplazame_ApiController::success(Aplazame_Sdk_Serializer_JsonSerializer::serializeValue($historyOrders));
    }
}
