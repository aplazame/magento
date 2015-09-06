<?php

class Aplazame_Aplazame_Model_Api_Client extends Varien_Object
{
    const USER_AGENT = 'AplazameMagento/';
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

        $client->setHeaders('User-Agent: '. self::USER_AGENT .
            Mage::getConfig()->getModuleConfig('Aplazame_Aplazame')->version);

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

    public function authorize()
    {
        return $this->_api_request(Varien_Http_Client::POST, "/" .
            $this->getOrderId() . "/authorize");
    }

    public function updateOrder()
    {
        $order = $this->getOrder();

        $serializer = Mage::getModel('aplazame/api_serializers');
        $data = $serializer->setOrder($order)->getOrderUpdate();

        return $this->_api_request(
            'PATCH', "/" . (int)$order->getIncrementId(), $data);
    }

    public function cancelOrder()
    {
        $order = $this->getOrder();

        return $this->_api_request(
            Varien_Http_Client::POST, "/" . (int)$order->getIncrementId() . "/cancel");
    }
}
