<?php

class Aplazame_Aplazame_Block_Checkout_Cart_Widget extends Mage_Core_Block_Template
{
    /**
     * @var Mage_Sales_Model_Quote $_product
     */
    protected $_quote;

    /**
     * @return Mage_Core_Helper_Abstract|Aplazame_Aplazame_Helper_Data
     */
    public function getAplazameHelper()
    {
        return Mage::helper('aplazame');
    }

    /**
     * Devuelve el quote actual
     *
     * @return Mage_Sales_Model_Quote|mixed
     */
    public function getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        return $this->_quote;
    }

    /**
     * Devuelve el final price del producto
     *
     * @return float
     */
    public function getTotal()
    {
        if ($this->getQuote() instanceof Mage_Sales_Model_Quote) {
            $total = $this->getQuote()->getGrandTotal();
        } else {
            $total = 0;
        }

        return $total;
    }

    public function getShowLegalAdvice()
    {
        return $this->getAplazameHelper()->isCartWidgetLegalAdviceEnabled() ? 'true' : 'false';
    }

    public function _toHtml()
    {
        if (!$this->getAplazameHelper()->isCartWidgetEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
