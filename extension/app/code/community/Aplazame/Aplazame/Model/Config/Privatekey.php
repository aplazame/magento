<?php

class Aplazame_Aplazame_Model_Config_Privatekey extends Mage_Adminhtml_Model_System_Config_Backend_Encrypted
{
    public static function setAplazameMerchantParams($privateKey)
    {
        $client = new Aplazame_Sdk_Api_Client(
            getenv('APLAZAME_API_BASE_URI') ? getenv('APLAZAME_API_BASE_URI') : 'https://api.aplazame.com',
            (Mage::getStoreConfig('payment/aplazame/sandbox') ? Aplazame_Sdk_Api_Client::ENVIRONMENT_SANDBOX : Aplazame_Sdk_Api_Client::ENVIRONMENT_PRODUCTION),
            $privateKey
        );

        $response = $client->patch('/me', [
            'confirmation_url' => Mage::getUrl('aplazame/api/index', [
                '_query' => [
                    'path' => '/confirm/',
                ],
                '_nosid' => true,
                '_store' => Mage::app()->getDefaultStoreView(),
            ]),
        ]);

        return $response;
    }

    /**
     * @var Mage_Core_Model_Config_Data
     */
    protected $_configData;

    /**
     * @var string
     */
    protected $_publicApiKey;

    public function _construct() 
    {
        parent::_construct();

        $this->_configData = Mage::getModel('core/config_data');
    }

    public function _beforeSave()
    {
        try {
            $response = self::setAplazameMerchantParams($this->getValue());
        } catch (Aplazame_Sdk_Api_ApiClientException $apiClientException) {
            $label = $this->getData('field_config/label');
            Mage::throwException($this->__($label . ' ' . $apiClientException->getMessage()));
        }

        $this->_publicApiKey = $response['public_api_key'];

        return parent::_beforeSave();
    }

    public function _afterSave()
    {
        $path = 'payment/aplazame/public_api_key';
        $this->_configData
            ->load($path, 'path')
            ->setValue($this->_publicApiKey)
            ->setPath($path)
            ->save();

        return parent::_afterSave();
    }
}
