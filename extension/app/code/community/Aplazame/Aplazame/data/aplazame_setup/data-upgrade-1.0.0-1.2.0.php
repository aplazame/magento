<?php

$privateKey = Mage::getStoreConfig('payment/aplazame/secret_api_key');
if (!empty($privateKey)) {
    /** @var Mage_Core_Model_Config_Data $configData */
    $configData = Mage::getModel('core/config_data');

    Aplazame_Aplazame_Model_Config_Privatekey::setAplazameMerchantParams($privateKey);
}
