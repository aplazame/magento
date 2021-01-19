<?php

class Aplazame_Aplazame_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/active');
    }

    // Product widget

    public function isProductWidgetEnabled()
    {
        return $this->isEnabled() && (bool) Mage::getStoreConfig('payment/aplazame/product_widget_enabled');
    }

    public function isProductWidgetLegalAdviceEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/product_legal_advice');
    }

    public function getProductDefaultInstalments()
    {
        return (int) Mage::getStoreConfig('payment/aplazame/product_default_instalments');
    }

    // Cart widget

    public function isCartWidgetEnabled()
    {
        return $this->isEnabled() && (bool) Mage::getStoreConfig('payment/aplazame/cart_widget_enabled');
    }

    public function isCartWidgetLegalAdviceEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/cart_legal_advice');
    }

    public function getCartDefaultInstalments()
    {
        return (int) Mage::getStoreConfig('payment/aplazame/cart_default_instalments');
    }
}
