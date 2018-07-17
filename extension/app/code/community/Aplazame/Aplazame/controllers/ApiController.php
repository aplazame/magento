<?php

class Aplazame_Aplazame_ApiController extends Mage_Core_Controller_Front_Action
{
    public static function forbidden()
    {
        return array(
            'status_code' => 403,
            'payload' => array(
                'status' => 403,
                'type' => 'FORBIDDEN',
            ),
        );
    }

    public static function not_found()
    {
        return array(
            'status_code' => 404,
            'payload' => array(
                'status' => 404,
                'type' => 'NOT_FOUND',
            ),
        );
    }

    public static function client_error($detail)
    {
        return array(
            'status_code' => 400,
            'payload' => array(
                'status' => 400,
                'type' => 'CLIENT_ERROR',
                'detail' => $detail,
            ),
        );
    }

    public static function success(array $payload)
    {
        return array(
            'status_code' => 200,
            'payload' => $payload,
        );
    }

    public static function collection($page, $page_size, array $elements)
    {
        return self::success(
            array(
                'query' => array(
                    'page' => $page,
                    'page_size' => $page_size,
                ),
                'elements' => $elements,
            )
        );
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        $path = $request->getParam('path', '');
        $queryArguments = $request->getParams();
        $payload = json_decode($request->getRawBody(), true);

        $result = $this->route($path, $queryArguments, $payload);

        $response = $this->getResponse();
        $response->setHttpResponseCode($result['status_code']);
        $response->setHeader('Content-type', 'application/json');
        $response->setBody(json_encode(Aplazame_Sdk_Serializer_JsonSerializer::serializeValue($result['payload'])));
    }

    /**
     * @param string $path
     * @param array $queryArguments
     * @param null|array $payload
     *
     * @return array
     */
    public function route($path, array $queryArguments, $payload)
    {
        if (!$this->verifyAuthentication()) {
            return self::forbidden();
        }

        switch ($path) {
            case '/article/':
                $controller = new Aplazame_Aplazame_Api_Article(Mage::getResourceModel('catalog/product_collection'));

                return $controller->articles($queryArguments);
            case '/confirm/':
                $controller = new Aplazame_Aplazame_Api_Confirm(
                    Mage::getModel('sales/order'),
                    Mage::getStoreConfig('payment/aplazame/sandbox')
                );

                return $controller->confirm($payload);
            case '/order/{order_id}/history/':
                $controller = new Aplazame_Aplazame_Api_Order(Mage::getModel('sales/order'));

                return $controller->history($queryArguments);
            default:
                return self::not_found();
        }
    }

    /**
     * @return bool
     */
    protected function verifyAuthentication()
    {
        $privateKey = Mage::getStoreConfig('payment/aplazame/secret_api_key');

        $authorization = $this->getAuthorizationFromRequest();
        if (!$authorization || empty($privateKey)) {
            return false;
        }

        return ($authorization === $privateKey);
    }

    protected function getAuthorizationFromRequest()
    {
        $token = $this->getRequest()->getParam('access_token');
        if ($token) {
            return $token;
        }

        return false;
    }
}
