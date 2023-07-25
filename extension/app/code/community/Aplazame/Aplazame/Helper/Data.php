<?php

class Aplazame_Aplazame_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/active');
    }

    public function isWidgetLegacyEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/widget_legacy_enabled');
    }

    public function getWidgetOutOfLimits()
    {
        return Mage::getStoreConfig('payment/aplazame/widget_out_of_limits');
    }

    // Product widget

    public function isProductWidgetEnabled()
    {
        return $this->isEnabled() && (bool) Mage::getStoreConfig('payment/aplazame/product_widget_enabled');
    }

    public function isProductWidgetDownpaymentInfoEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/product_downpayment_info');
    }

    public function isProductWidgetLegalAdviceEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/product_legal_advice');
    }

    public function isProductWidgetPayIn4Enabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/product_pay_in_4');
    }

    public function isProductWidgetBorderEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/product_widget_border');
    }

    public function isProductWidgetMaxDesiredEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/product_widget_max_desired');
    }

    public function getProductDefaultInstalments()
    {
        return (int) Mage::getStoreConfig('payment/aplazame/product_default_instalments');
    }

    public function getProductPrimaryColor()
    {
        return Mage::getStoreConfig('payment/aplazame/product_widget_primary_color');
    }

    public function getProductLayout()
    {
        return Mage::getStoreConfig('payment/aplazame/product_widget_layout');
    }

    public function getProductAlign()
    {
        return Mage::getStoreConfig('payment/aplazame/product_widget_align');
    }

    // Cart widget

    public function isCartWidgetEnabled()
    {
        return $this->isEnabled() && (bool) Mage::getStoreConfig('payment/aplazame/cart_widget_enabled');
    }

    public function isCartWidgetDownpaymentInfoEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/cart_downpayment_info');
    }

    public function isCartWidgetLegalAdviceEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/cart_legal_advice');
    }

    public function isCartWidgetPayIn4Enabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/cart_pay_in_4');
    }

    public function getCartDefaultInstalments()
    {
        return (int) Mage::getStoreConfig('payment/aplazame/cart_default_instalments');
    }

    public function isCartWidgetMaxDesiredEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/aplazame/cart_widget_max_desired');
    }

    public function getCartPrimaryColor()
    {
        return Mage::getStoreConfig('payment/aplazame/cart_widget_primary_color');
    }

    public function getCartLayout()
    {
        return Mage::getStoreConfig('payment/aplazame/cart_widget_layout');
    }

    public function getCartAlign()
    {
        return Mage::getStoreConfig('payment/aplazame/cart_widget_align');
    }
}
