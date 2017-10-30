<?php

$privateKey = Mage::getStoreConfig('payment/aplazame/secret_api_key');
if (!empty($privateKey)) {
    /** @var Mage_Core_Model_Config_Data $configData */
    $configData = Mage::getModel('core/config_data');
    $sandbox = Mage::getStoreConfig('payment/aplazame/sandbox');

    $client = new Aplazame_Sdk_Api_Client(
        getenv('APLAZAME_API_BASE_URI') ? getenv('APLAZAME_API_BASE_URI') : 'https://api.aplazame.com',
        (Mage::getStoreConfig('payment/aplazame/sandbox') ? Aplazame_Sdk_Api_Client::ENVIRONMENT_SANDBOX : Aplazame_Sdk_Api_Client::ENVIRONMENT_PRODUCTION),
        $privateKey
    );

    $confirmationUrl = Mage::getUrl(
        'aplazame/api/index',
        array(
            '_query' => array(
                'path' => '/confirm/',
            ),
            '_nosid' => true,
        )
    );

    $response = $client->patch('/me', array(
        'confirmation_url' => $confirmationUrl,
    ));

    $publicKey = $response['public_api_key'];

    $path = 'payment/aplazame/public_api_key';
    $configData
        ->load($path, 'path')
        ->setValue($publicKey)
        ->setPath($path)
        ->save()
    ;

    $path = 'payment/aplazame/refund_method_magento_native';
    $configData
        ->load($path, 'path')
        ->setValue(false)
        ->setPath($path)
        ->save()
    ;
}
