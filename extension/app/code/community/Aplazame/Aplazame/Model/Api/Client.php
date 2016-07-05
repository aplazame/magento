<?php

/**
 * @method $this setOrder(Mage_Sales_Model_Resource_Order $order)
 * @method Mage_Sales_Model_Resource_Order getOrder()
 */
class Aplazame_Aplazame_Model_Api_Client extends Varien_Object
{
    const API_CHECKOUT_PATH = '/orders';

    public function __construct()
    {
    }

    public function getBaseApiUrl()
    {
        return str_replace('://', '://api.', Mage::getStoreConfig('payment/aplazame/host'));
    }

    protected function _api_request($method, $path, $data=null)
    {
        $url = trim($this->getBaseApiUrl(), "/") . self::API_CHECKOUT_PATH . $path;
        $client = new Zend_Http_Client($url);

        if (in_array($method, array(
                Zend_Http_Client::POST, Zend_Http_Client::PUT, 'PATCH')) && $data) {
            $client->setHeaders('Content-type: application/json');
            $client->setRawData(json_encode($data), 'application/json');
        }

        $client->setHeaders('Authorization: Bearer '.
            Mage::getStoreConfig('payment/aplazame/secret_api_key'));

        $version = Mage::getStoreConfig('payment/aplazame/version');

        if ($version) {
            $version = explode(".", $version);
            $version = $version[0];
        }

        $versions = array(
            'PHP/' . PHP_VERSION,
            'Magento/' . Mage::getVersion(),
            'AplazameMagento/' . Mage::getConfig()->getModuleConfig('Aplazame_Aplazame')->version,
        );

        $client->setHeaders('User-Agent: '. implode(', ', $versions));

        $client->setHeaders('Accept: '. 'application/vnd.aplazame.'.
            (Mage::getStoreConfig('payment/aplazame/sandbox')?'sandbox.': '') . $version . '+json');

        $response = $client->request($method);
        $raw_result = $response->getBody();
        $status_code = $response->getStatus();

        if ($status_code >= 500) {
            Mage::throwException(Mage::helper('aplazame')->__(
                'Aplazame error code: ' . $status_code));
        }

        try {
            $ret_json = Zend_Json::decode($raw_result, Zend_Json::TYPE_ARRAY);
        } catch (Zend_Json_Exception $e) {
            Mage::throwException(Mage::helper('aplazame')->__(
                'Invalid api response: '. $raw_result));
        }

        if ($status_code >= 400) {
            $errorMsg = Mage::helper('aplazame')->__('Aplazame error code ' . $status_code . ': '
                . $ret_json['error']['message']);

            if ($status_code == 403) {
                //no tiramos exception, pero informamos de error
                Mage::getSingleton('adminhtml/session')->addError($errorMsg);
            }

            //para los demás errores mayores de 400 menos el 403
            //tiramos exception.
            Mage::throwException($errorMsg);
        }

        return $ret_json;
    }

    /**
     * @param string $orderId
     * @return array
     */
    public function authorize($orderId)
    {
        return $this->_api_request(Varien_Http_Client::POST, "/" . $orderId . "/authorize");
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function updateOrder($order)
    {
        /** @var Aplazame_Aplazame_Model_Api_Serializers $serializer */
        $serializer = Mage::getModel('aplazame/api_serializers');
        $data = $serializer->getOrderUpdate($order);

        return $this->_api_request(
            'PATCH', "/" . (int)$order->getIncrementId(), $data);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function cancelOrder($order)
    {
        return $this->_api_request(
            Varien_Http_Client::POST, "/" . (int)$order->getIncrementId() . "/cancel");
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param float $amount
     * @return array
     */
    public function refundAmount($order, $amount)
    {
        $data = array('amount'=>Aplazame_Util::formatDecimals($amount));

        return $this->_api_request(
            Varien_Http_Client::POST, "/" . (int)$order->getIncrementId() . "/refund", $data);
    }
}
