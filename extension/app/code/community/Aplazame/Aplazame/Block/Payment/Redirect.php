<?php


class Aplazame_Aplazame_Block_Payment_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $payment = Mage::getModel('aplazame/payment');

        $html = '
<html>
    <body style="margin: 0;">

        <script
            type="text/javascript"
            src="'. trim(Mage::getStoreConfig('payment/aplazame/host'), "/") . '/static/aplazame.js' . '"
            data-aplazame="publicKey: '. Mage::getStoreConfig('payment/aplazame/public_api_key') . '"
            data-version="'. Mage::getStoreConfig('payment/aplazame/version') . '"
            data-sandbox="'. (Mage::getStoreConfig('payment/aplazame/sandbox')?'true':'false') . '">
        </script>

        <script>
            aplazame.checkout(' . json_encode($payment->getCheckoutSerializer(), 128) . ');
        </script>

        <iframe src="'./*Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)*/Mage::getUrl('', array('_secure'=>true)).'" style="position:fixed; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%; border:none; margin:0; padding:0; overflow:hidden;">
            Your browser doesnt support IFrames
        </iframe>
    </body>
</html>';

        return $html;
    }
}
