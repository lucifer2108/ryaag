<?php

namespace Thelia\Model;

use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Base\CartItem as BaseCartItem;

use Thelia\Core\Event\Cart\CartEvent;
use Thelia\TaxEngine\Calculator;

class CartItem extends BaseCartItem
{
    protected $dispatcher;

    public function setDisptacher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function postInsert(ConnectionInterface $con = null)
    {
        if ($this->dispatcher) {
            $cartEvent = new CartEvent($this->getCart());

            $this->dispatcher->dispatch(TheliaEvents::AFTER_CARTADDITEM, $cartEvent);
        }
    }

    public function postUpdate(ConnectionInterface $con = null)
    {
        if ($this->dispatcher) {
            $cartEvent = new CartEvent($this->getCart());

            $this->dispatcher->dispatch(TheliaEvents::AFTER_CARTUPDATEITEM, $cartEvent);
        }
    }

    /**
     * @param $value
     * @return $this
     */
    public function updateQuantity($value)
    {
        $currentQuantity = $this->getQuantity();

        if ($value <= 0) {
            $value = $currentQuantity;
        }

        if (ConfigQuery::checkAvailableStock()) {
            $productSaleElements = $this->getProductSaleElements();

            if ($productSaleElements->getQuantity() < $value) {
                $value = $currentQuantity;
            }
        }

        $this->setQuantity($value);

        return $this;
    }

    public function addQuantity($value)
    {
        $currentQuantity = $this->getQuantity();
        $newQuantity = $currentQuantity + $value;

        if ($value <= 0) {
            $value = $currentQuantity;
        }

        if (ConfigQuery::checkAvailableStock()) {
            $productSaleElements = $this->getProductSaleElements();

            if ($productSaleElements->getQuantity() < $newQuantity) {
                $newQuantity = $currentQuantity;
            }
        }

        $this->setQuantity($newQuantity);

        return $this;
    }

    public function getRealPrice()
    {
        return $this->getPromo() == 1 ? $this->getPromoPrice() : $this->getPrice();
    }

    public function getProduct(ConnectionInterface $con = null, $locale = null)
    {
        $product = parent::getProduct($con);

        $translation = $product->getTranslation($locale);

        if ($translation->isNew()) {
            if (ConfigQuery::getDefaultLangWhenNoTranslationAvailable()) {
                $locale = Lang::getDefaultLanguage()->getLocale();
            }
        }

        $product->setLocale($locale);

        return $product;
    }

    public function getRealTaxedPrice(Country $country)
    {
        return $this->getPromo() == 1 ? $this->getTaxedPromoPrice($country) : $this->getTaxedPrice($country);
    }

    public function getTaxedPrice(Country $country)
    {
        $taxCalculator = new Calculator();

        return round($taxCalculator->load($this->getProduct(), $country)->getTaxedPrice($this->getPrice()), 2);
    }

    public function getTaxedPromoPrice(Country $country)
    {
        $taxCalculator = new Calculator();

        return round($taxCalculator->load($this->getProduct(), $country)->getTaxedPrice($this->getPromoPrice()), 2);
    }
}
