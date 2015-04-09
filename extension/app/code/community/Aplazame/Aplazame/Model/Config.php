<?php

class Aplazame_Aplazame_Model_Config
{
    protected $_storeId = null;

    /**
     * Return merchant country code
     */
    public function getMerchantCountry()
    {
        $countryCode = Mage::getStoreConfig("aplazame/general/merchant_country", $this->_storeId);
        if (!$countryCode) {
            $countryCode = Mage::helper('core')->getDefaultCountry($this->_storeId);
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
