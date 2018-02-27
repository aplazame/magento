<?php

$privateKey = Mage::getStoreConfig('payment/aplazame/secret_api_key');
if (!empty($privateKey)) {
    /** @var Mage_Core_Model_Config_Data $configData */
    $configData = Mage::getModel('core/config_data');

    $response = Aplazame_Aplazame_Model_Config_Privatekey::setAplazameMerchantParams($privateKey);
    $publicKey = $response['public_api_key'];

    $path = 'payment/aplazame/public_api_key';
    $configData
        ->load($path, 'path')
        ->setValue($publicKey)
        ->setPath($path)
        ->save();

    $path = 'payment/aplazame/refund_method_magento_native';
    $configData
        ->load($path, 'path')
        ->setValue(false)
        ->setPath($path)
        ->save();
}
