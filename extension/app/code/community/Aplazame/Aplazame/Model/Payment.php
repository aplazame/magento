<?php


require_once Mage::getBaseDir('lib').DS.'Aplazame'.DS.'Aplazame.php';


class Aplazame_Aplazame_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    const METHOD_CODE = 'aplazame';
    const CHECKOUT_TOKEN = 'checkout_token';

    /**
     * options
     */
    protected $_code = self::METHOD_CODE;
    protected $_formBlockType = 'aplazame/payment_form';
    protected $_infoBlockType = 'aplazame/payment_info';
    protected $_isInitializeNeeded     = true;
    protected $_canUseInternal         = false;
    protected $_canUseForMultishipping = false;


    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;

        $stateObject->setState($state);
        $stateObject->setStatus($state);
        $stateObject->setIsNotified(false);
    }

    /**
     * Whether method is available for specified currency
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->getConfig()->isCurrencyCodeSupported($currencyCode);
    }

    private static function _orderTotal($order)
    {
        return $order->getTotalDue();
    }

    /**
     * Config instance getter
     */
    public function getConfig()
    {
        if (null === $this->_config) {
            $params = array($this->_code);
            if ($store = $this->getStore()) {
                $params[] = is_object($store) ? $store->getId() : $store;
            }
            $this->_config = Mage::getModel('aplazame/config', $params);
        }
        return $this->_config;
    }

    /**
     * Get aplazame session namespace
     */
    public function getSession()
    {
        return Mage::getSingleton('aplazame/session');
    }

    /**
     * Get checkout session namespace
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }


    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('aplazame/payment/redirect', array('_secure' => true));
    }

    public function getChargeId()
    {
        return $this->getInfoInstance()->getAdditionalInformation("charge_id");
    }

    protected function _validate_amount_result($amount, $result)
    {
        if ($result["amount"] != $amount) {
            Mage::throwException(Mage::helper('aplazame')->__(
                'Aplazame authorized amount of ' . $result["amount"] .
                ' does not match requested amount of: ' . $amount));
        }
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        if ($amount <= 0) {
            Mage::throwException(Mage::helper('aplazame')->__('Invalid amount for authorization.'));
        }

        $token = $payment->getAdditionalInformation(self::CHECKOUT_TOKEN);

        $api = Mage::getModel('aplazame/api_client');
        $result = $api->setOrderId($token)->authorize();

        if (isset($result["id"])) {
            $this->getInfoInstance()->setAdditionalInformation("charge_id", $result["id"]);
        } else {
            Mage::throwException(Mage::helper('aplazame')->__('Aplazame charge id not returned from call.'));
        }

        $this->_validate_amount_result(Aplazame_Util::formatDecimals($amount), $result);
        $payment->setTransactionId($this->getChargeId())->setIsTransactionClosed(0);
        return $this;
    }

    public function processConfirmOrder($order, $checkout_token)
    {
        $payment = $order->getPayment();

        $payment->setAdditionalInformation(self::CHECKOUT_TOKEN, $checkout_token);
        $action = $this->getConfigData('payment_action');

        //authorize the total amount.
        $payment->authorize(true, static::_orderTotal($order));
        $payment->setAmountAuthorized(static::_orderTotal($order));
        $order->save();
    }

    public function processHistory($order, $checkout_token)
    {
        $serializer = Mage::getModel('aplazame/api_serializers');

        $result = $serializer->setOrder($order)
                             ->getHistory();

        return json_encode($result, 128);
    }

    public function getCheckoutSerializer()
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $serializer = Mage::getModel('aplazame/api_serializers');

        $serializer->setOrder($order);
        return $serializer->getCheckout();
    }
}
