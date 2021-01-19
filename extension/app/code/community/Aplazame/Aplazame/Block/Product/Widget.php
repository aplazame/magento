<?php

class Aplazame_Aplazame_Block_Product_Widget extends Mage_Core_Block_Template
{
    /**
     * @var Mage_Catalog_Model_Product $_product
     */
    protected $_product;

    /**
     * @return Mage_Core_Helper_Abstract|Aplazame_Aplazame_Helper_Data
     */
    public function getAplazameHelper()
    {
        return Mage::helper('aplazame');
    }

    /**
     * Devuelve el current product cuando estamos en ficha de producto
     *
     * @return Mage_Catalog_Model_Product|mixed
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = Mage::registry('current_product');
        }

        return $this->_product;
    }

    /**
     * Devuelve el final price del producto
     *
     * @return float
     */
    public function getFinalPrice()
    {
        if ($this->getProduct() instanceof Mage_Catalog_Model_Product) {
            $total = Mage::app()->getStore()->convertPrice($this->getProduct()->getFinalPrice());
        } else {
            $total = 0;
        }

        return $total;
    }

    public function getShowLegalAdvice()
    {
        return $this->getAplazameHelper()->isProductWidgetLegalAdviceEnabled() ? 'true' : 'false';
    }

    public function getDefaultInstalments()
    {
        return $this->getAplazameHelper()->getProductDefaultInstalments();
    }

    public function _toHtml()
    {
        if (!$this->getAplazameHelper()->isProductWidgetEnabled()) {
            return '';
        }

        if (!($this->getProduct() instanceof Mage_Catalog_Model_Product)) {
            return '';
        }

        return parent::_toHtml();
    }
}
