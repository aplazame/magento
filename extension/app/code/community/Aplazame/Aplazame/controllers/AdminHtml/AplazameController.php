<?php

class Aplazame_Aplazame_AdminHtml_AplazameController extends Mage_Adminhtml_Controller_Action
{
    /** @var Aplazame_Aplazame_Model_Api_Client */
    protected $_aplazameClient;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);

        $this->_aplazameClient = Mage::getModel('aplazame/api_client');
    }

    public function proxyAction()
    {
        $request = $this->getRequest();

        $data = json_decode($request->getParam('data'));

        $aplazameResponse = $this->_aplazameClient->apiClient->request(
            $request->getParam('method'),
            $request->getParam('path'),
            $data
        );

        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode($aplazameResponse));
    }

    public function productCampaignsAction()
    {
        $productId = $this->getRequest()->getParam('id');

        $this->loadLayout();

        $block = $this->getLayout()->createBlock('aplazame/adminHtml_productCampaigns');
        if (!($block instanceof Aplazame_Aplazame_Block_AdminHtml_ProductCampaigns)) {
            throw new LogicException('Expected Aplazame_Aplazame_Block_AdminHtml_ProductCampaigns');
        }

        $block->setArticlesId(array($productId));

        $this->getResponse()->setBody($block->toHtml());
    }

    public function productsCampaignsAction()
    {
        $products = $this->getRequest()->getParam('product');

        $this->loadLayout();

        $block = $this->getLayout()->createBlock('aplazame/adminHtml_productsCampaigns');
        if (!($block instanceof Aplazame_Aplazame_Block_AdminHtml_ProductsCampaigns)) {
            throw new LogicException('Expected Aplazame_Aplazame_Block_AdminHtml_ProductsCampaigns');
        }

        $block->setArticlesId($products);

        $this->getLayout()->getBlock('content')->append($block);

        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin');
    }
}
