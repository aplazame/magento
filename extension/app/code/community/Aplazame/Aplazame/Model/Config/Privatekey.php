<?php

class Aplazame_Aplazame_Model_Config_Privatekey extends Mage_Adminhtml_Model_System_Config_Backend_Encrypted
{
    /**
     * @var Mage_Core_Model_Config_Data
     */
    private $configData;

    /**
     * @var string
     */
    private $publicApiKey;

    public function _construct() {
        parent::_construct();

        $this->configData = Mage::getModel('core/config_data');
    }

    public function _beforeSave()
    {
        $client = new Aplazame_Sdk_Api_Client(
            getenv('APLAZAME_API_BASE_URI') ? getenv('APLAZAME_API_BASE_URI') : 'https://api.aplazame.com',
            (Mage::getStoreConfig('payment/aplazame/sandbox') ? Aplazame_Sdk_Api_Client::ENVIRONMENT_SANDBOX : Aplazame_Sdk_Api_Client::ENVIRONMENT_PRODUCTION),
            $this->getValue()
        );

        try {
            $response = $client->get('/me');
        } catch (Aplazame_Sdk_Api_ApiClientException $apiClientException) {
            $label = $this->getData('field_config/label');
            Mage::throwException($this->__($label . ' ' . $apiClientException->getMessage()));
        }

        $this->publicApiKey = $response['public_api_key'];

        return parent::_beforeSave();
    }

    public function _afterSave()
    {
        $path = 'payment/aplazame/public_api_key';
        $this->configData
            ->load($path, 'path')
            ->setValue($this->publicApiKey)
            ->setPath($path)
            ->save()
        ;

        return parent::_afterSave();
    }
}
