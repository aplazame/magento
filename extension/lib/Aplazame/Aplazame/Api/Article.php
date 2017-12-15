<?php

final class Aplazame_Aplazame_Api_Article
{
    /** @var Mage_Catalog_Model_Resource_Product_Collection */
    private $productCollection;

    public function __construct(Mage_Catalog_Model_Resource_Product_Collection $productCollection)
    {
        $this->productCollection = $productCollection;
    }

    public function articles(array $queryArguments)
    {
        $page = (isset($queryArguments['page'])) ? $queryArguments['page'] : 1;
        $page_size = (isset($queryArguments['page_size'])) ? $queryArguments['page_size'] : 10;

        $products = $this->productCollection
            ->addAttributeToSelect('*')
            ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
            ->setPage($page, $page_size)
        ;

        $articles = array_map(array('Aplazame_Aplazame_Api_BusinessModel_Article', 'createFromProduct'), $products->getItems());

        return Aplazame_Aplazame_ApiController::collection($page, $page_size, $articles);
    }
}
