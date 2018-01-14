<?php

/**
 * Article.
 */
class Aplazame_Aplazame_BusinessModel_Article
{
    public static function crateFromOrderItem(Mage_Sales_Model_Order_Item $orderItem)
    {
        $productId = $orderItem->getProductId();
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')->load($productId);

        $originalPrice = max($orderItem->getPrice(), $product->getPrice());
        $discounts = abs($product->getPrice() - $orderItem->getPrice());

        $aArticle = new self();
        $aArticle->id = $productId;
        $aArticle->name = $orderItem->getName();
        $aArticle->url = $product->getProductUrl();
        $aArticle->image_url = self::getProductImage($product);
        $aArticle->quantity = intval($orderItem->getQtyOrdered());
        $aArticle->price = Aplazame_Sdk_Serializer_Decimal::fromFloat($originalPrice);
        $aArticle->description = substr($product->getDescription(), 0, 255);
        $aArticle->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat($orderItem->getTaxPercent());
        $aArticle->discount = Aplazame_Sdk_Serializer_Decimal::fromFloat($discounts);

        return $aArticle;
    }

    public static function getProductImage(Mage_Catalog_Model_Product $product)
    {
        /** @var Mage_Catalog_Helper_Image $imageHelper */
        $imageHelper = Mage::helper('catalog/image');

        return (string) $imageHelper->init($product, 'image');
    }
}
