<?php

final class Aplazame_Aplazame_Api_Confirm
{
    /** @var Mage_Sales_Model_Order */
    private $orderModel;

    /** @var bool */
    private $sandbox;

    private static function ok()
    {
        return array(
            'status_code' => 200,
            'payload' => array(
                'status' => 'ok',
            ),
        );
    }

    private static function ko()
    {
        return array(
            'status_code' => 200,
            'payload' => array(
                'status' => 'ko',
            ),
        );
    }

    public function __construct(Mage_Sales_Model_Order $orderModel, $sandbox)
    {
        $this->orderModel = $orderModel;
        $this->sandbox = (bool) $sandbox;
    }

    public function confirm($payload)
    {
        if (!$payload) {
            return Aplazame_Aplazame_ApiController::client_error('Payload is malformed');
        }

        if (!isset($payload['sandbox']) || $payload['sandbox'] !== $this->sandbox) {
            return Aplazame_Aplazame_ApiController::client_error('"sandbox" not provided');
        }

        if (!isset($payload['mid'])) {
            return Aplazame_Aplazame_ApiController::client_error('"mid" not provided');
        }
        $checkout_token = $payload['mid'];

        $order = $this->orderModel->loadByIncrementId($checkout_token);
        if (!$order->getId()) {
            return Aplazame_Aplazame_ApiController::not_found();
        }

        $payment = $order->getPayment();

        $amount = $order->getGrandTotal();
        if ($payload['total_amount'] !== Aplazame_Sdk_Serializer_Decimal::fromFloat($amount)->jsonSerialize() ||
            $payload['currency']['code'] !== $order->getBaseCurrencyCode()
        ) {
            $payment->setIsFraudDetected(true);
        }

        switch ($payload['status']) {
            case 'pending':
                switch ($payload['status_reason']) {
                    case 'confirmation_required':
                        $payment->accept();
                        $order->save();
                        break;
                }
                break;
            case 'ko':
                $payment->deny();
                $order->save();
                break;
        }

        if ($payment->getIsFraudDetected()) {
            return self::ko();
        }

        return self::ok();
    }
}
