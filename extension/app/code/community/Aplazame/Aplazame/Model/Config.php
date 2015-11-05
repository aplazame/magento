<?php

class Aplazame_Aplazame_Model_Config
{

    const XML_PATH_DEFAULT_COUNTRY              = 'general/country/default';

    protected $_storeId = null;

    /**
     * Return merchant country code
     */
    public function getMerchantCountry()
    {
        $countryCode = Mage::getStoreConfig("aplazame/general/merchant_country", $this->_storeId);
        if (!$countryCode) {
            $countryCode = Mage::getStoreConfig(self::XML_PATH_DEFAULT_COUNTRY, $this->_storeId);
        }
        return $countryCode;
    }




    /**
     * Check whether specified currency code is supported
     */
    public function isCurrencyCodeSupported($code)
    {
        if ($this->getMerchantCountry() == 'ES' && $code == 'EUR') {
            return true;
        }
        return false;
    }
}