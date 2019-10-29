<?php

class Aplazame_Aplazame_Block_Payment_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        switch (Mage::app()->getRequest()->getParam('type')) {
            case 'instalments':
                /** @var Aplazame_Aplazame_Model_Payment $payment */
                $payment = Mage::getModel('aplazame/payment');
                break;
            case 'pay_later':
                /** @var Aplazame_Aplazame_Model_PaymentPayLater $payment */
                $payment = Mage::getModel('aplazame/paymentPayLater');
                break;
            default:
                return $this->defaultHtml();
        }

        $aplazameJsUri = getenv('APLAZAME_JS_URI') ? getenv('APLAZAME_JS_URI') : 'https://cdn.aplazame.com/aplazame.js';

        $aplazameJsParams = http_build_query(
            array(
                'public_key' => Mage::getStoreConfig('payment/aplazame/public_api_key'),
                'sandbox' => Mage::getStoreConfig('payment/aplazame/sandbox') ? 'true' : 'false',
            )
        );

        $payload = $payment->createCheckoutOnAplazame();

        $html = '
<html>
    <body style="margin: 0;">

        <script
            type="text/javascript" src="' . $aplazameJsUri . '?' . $aplazameJsParams . '"
            async defer
        ></script>

        <script>
            (window.aplazame = window.aplazame || []).push(function (aplazame) {
                aplazame.checkout("' . $payload['id'] . '")
            })
        </script>

        <iframe src="' . /*Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)*/Mage::getUrl('', array('_secure' => true)) . '" style="position:fixed; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%; border:none; margin:0; padding:0; overflow:hidden;">
            Your browser does not support IFrames
        </iframe>
    </body>
</html>';

        return $html;
    }

    public function defaultHtml()
    {
        return '
<html>
	<head>
	   <meta http-equiv="refresh" content="0; url=' . /*Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)*/Mage::getUrl('', array('_secure' => true)) . '">
	</head>
	<body>
	   <p>404 Page not found. You might want to check that URL again or head over to our 
	   <a href="' . /*Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)*/Mage::getUrl('', array('_secure' => true)) . '">homepage.</a></p>
	</body>
</html>';
    }
}
