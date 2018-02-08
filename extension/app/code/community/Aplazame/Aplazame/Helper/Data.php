<?php

class Aplazame_Aplazame_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/active');
    }

    public function isProductWidgetEnabled()
    {
        return $this->isEnabled() && (bool) Mage::getStoreConfig('payment/aplazame/product_widget_enabled');
    }

    public function isCartWidgetEnabled()
    {
        return $this->isEnabled() && (bool) Mage::getStoreConfig('payment/aplazame/cart_widget_enabled');
    }
}
