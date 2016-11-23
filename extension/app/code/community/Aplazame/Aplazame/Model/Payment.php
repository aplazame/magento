<?php

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
    /** @var Aplazame_Aplazame_Model_Config Config */
    private $_config;


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
     *
     * @return Mage_Checkout_Model_Session
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

        /** @var Aplazame_Aplazame_Model_Api_Client $api */
        $api = Mage::getModel('aplazame/api_client');
        $result = $api->authorize($token);

        if (isset($result["id"])) {
            $this->getInfoInstance()->setAdditionalInformation("charge_id", $result["id"]);
        } else {
            Mage::throwException(Mage::helper('aplazame')->__('Aplazame charge id not returned from call.'));
        }

        $this->_validate_amount_result(Aplazame_Sdk_Serializer_Decimal::fromFloat($amount)->jsonSerialize(), $result);
        $payment->setTransactionId($this->getChargeId())->setIsTransactionClosed(0);
        return $this;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param mixed $checkout_token
     * @return $this
     */
    public function processConfirmOrder($order, $checkout_token)
    {
        $payment = $order->getPayment();

        $payment->setAdditionalInformation(self::CHECKOUT_TOKEN, $checkout_token);

        //authorize the total amount.
        $payment->authorize(true, self::_orderTotal($order));
        $payment->setAmountAuthorized(self::_orderTotal($order));


        if ((bool) $this->getConfigData('autoinvoice'))
        {
            //permitimos capturar en este caso, sino fallaria la generacion de factura
            $this->_canCapture = true;

            $invoice = $order->prepareInvoice();
            if ($invoice->getGrandTotal() > 0) { // Evitamos captar un pago con total cero.
                $invoice
                    ->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE)
                    ->register()
                    ->capture();
                $order->addRelatedObject($invoice);
                $payment->setCreatedInvoice($invoice);
                Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
            }
        }

        $order->save();

        return $this;
    }

    public function getCheckoutSerializer()
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        return Aplazame_Aplazame_BusinessModel_Checkout::createFromOrder($order);
    }
}
