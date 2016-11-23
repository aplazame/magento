<?php

class Aplazame_Aplazame_Model_Api_Client extends Varien_Object
{
    /**
     * @var Aplazame_Sdk_Api_Client
     */
    public $apiClient;

    public function __construct()
    {
        $this->apiClient = new Aplazame_Sdk_Api_Client(
            getenv('APLAZAME_API_BASE_URI') ? getenv('APLAZAME_API_BASE_URI') : 'https://api.aplazame.com',
            (Mage::getStoreConfig('payment/aplazame/sandbox') ? Aplazame_Sdk_Api_Client::ENVIRONMENT_SANDBOX : Aplazame_Sdk_Api_Client::ENVIRONMENT_PRODUCTION),
            Mage::getStoreConfig('payment/aplazame/secret_api_key'),
            new Aplazame_Aplazame_Http_ZendClient()
        );
    }

    /**
     * @param string $orderId
     * @return array
     */
    public function authorize($orderId)
    {
        return $this->apiClient->request(Varien_Http_Client::POST, '/orders/' . $orderId . "/authorize");
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function cancelOrder($order)
    {
        return $this->apiClient->request(
            Varien_Http_Client::POST, $this->getEndpointForOrder($order) . "/cancel");
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param float $amount
     * @return array
     */
    public function refundAmount($order, $amount)
    {
        $data = array('amount'=>Aplazame_Sdk_Serializer_Decimal::fromFloat($amount)->jsonSerialize());

        return $this->apiClient->request(
            Varien_Http_Client::POST, $this->getEndpointForOrder($order) . "/refund", $data);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function getEndpointForOrder($order)
    {
        return '/orders/' . $order->getIncrementId();
    }
}
