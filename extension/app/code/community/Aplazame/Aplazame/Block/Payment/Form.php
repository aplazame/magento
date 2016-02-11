<?php

require_once Mage::getBaseDir('lib').DS.'Aplazame'.DS.'Aplazame.php';


class Aplazame_Aplazame_Block_Payment_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setMethodLabel();
        $this->setTemplate('aplazame/payment/form.phtml');
    }

    private function setMethodLabel()
    {
        $this->setMethodTitle("");

        $logoSrc = 'https://aplazame.com/static/img/buttons/' . Mage::getStoreConfig('payment/aplazame/button_img') . '.png';
        $html = '<img src="' . $logoSrc . '" height="27" class="v-middle" />&nbsp;';
        $html.= 'Financia tu compa con Aplazame';

        $this->setMethodLabelAfterHtml($html);
    }

    public function getTotal()
    {
        return Aplazame_Util::formatDecimals(
            $this->getMethod()->getCheckout()->getQuote()->getGrandTotal());
    }

    /**
     * Devuelve el country ID en formato ISO 2 caracteres
     * para comunicarlo a aplazame y que pueda tomar decisiones en base al país de facturación.
     * @return string
     */
    public function getCountry()
    {
        $quote = Mage::getModel('checkout/cart')->getQuote();
        $countryId = $quote->getBillingAddress()->getCountryId();

        return $countryId;
    }
}
