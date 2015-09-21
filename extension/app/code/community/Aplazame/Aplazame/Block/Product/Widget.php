<?php

require_once Mage::getBaseDir('lib').DS.'Aplazame'.DS.'Aplazame.php';

class Aplazame_Aplazame_Block_Product_Widget Extends Mage_Core_Block_Template
{
    /**
     * @var Mage_Catalog_Model_Product $_product
     */
    protected $_product;


    /**
     * Devuelve el current product cuando estamos en ficha de producto
     * @return Mage_Catalog_Model_Product|mixed
     */
    public function getProduct()
    {
        if(!$this->_product)
        {
            $this->_product = Mage::registry('current_product');
        }

        return $this->_product;
    }

    /**
     * Devuelve el final price del producto, formateado para Aplazame
     * @return int
     */
    public function getFinalPrice()
    {
        if($this->getProduct() instanceof Mage_Catalog_Model_Product)
        {
            return Aplazame_Util::formatDecimals($this->getProduct()->getFinalPrice());
        }

        return Aplazame_Util::formatDecimals(0);
    }

    /**
     * Solo renderizamos si tenemos producto,
     * si no hay producto no renderizamos nada (empty string).
     *
     * @return string
     */
    public function _toHtml()
    {
        if($this->getProduct() instanceof Mage_Catalog_Model_Product)
        {
            return parent::_toHtml();
        }

        return 'No Product';
    }
}