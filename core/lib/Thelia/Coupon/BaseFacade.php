<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Thelia\Coupon;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;
use Thelia\Condition\ConditionEvaluator;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Template\ParserInterface;
use Thelia\Core\Template\TemplateHelper;
use Thelia\Model\AddressQuery;
use Thelia\Model\Coupon;
use Thelia\Model\CouponQuery;
use Thelia\Cart\CartTrait;
use Thelia\Model\Currency;
use Thelia\Model\CurrencyQuery;

/**
 * Allow to assist in getting relevant data on the current application state
 *
 * @package Coupon
 * @author  Guillaume MOREL <gmorel@openstudio.fr>
 *
 */
class BaseFacade implements FacadeInterface
{
    use CartTrait {
        CartTrait::getCart as getCartFromTrait;
    }

    /** @var ContainerInterface Service Container */
    protected $container = null;

    /** @var Translator Service Translator  */
    protected $translator = null;

    /** @var ParserInterface The thelia parser  */
    private $parser = null;

    /**
     * Constructor
     *
     * @param ContainerInterface $container Service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Return a Cart a CouponManager can process
     *
     * @return \Thelia\Model\Cart
     */
    public function getCart()
    {
        return $this->getCartFromTrait($this->getDispatcher(), $this->getRequest());
    }

    /**
     * Return an Address a CouponManager can process
     *
     * @return \Thelia\Model\Address
     */
    public function getDeliveryAddress()
    {
        try {
            return AddressQuery::create()->findPk($this->getRequest()->getSession()->getOrder()->getChoosenDeliveryAddress());
        } catch (\Exception $ex) {
            throw new \LogicException("Failed to get delivery address (" . $ex->getMessage() . ")");
        }
    }

    /**
     * Return an Customer a CouponManager can process
     *
     * @return \Thelia\Model\Customer
     */
    public function getCustomer()
    {
        return $this->container->get('thelia.securityContext')->getCustomerUser();
    }

    /**
     * Return Checkout total price
     *
     * @return float
     */
    public function getCheckoutTotalPrice()
    {
        return $this->getRequest()->getSession()->getOrder()->getTotalAmount();
    }

    /**
     * Return Checkout total postage (only) price
     *
     * @return float
     */
    public function getCheckoutPostagePrice()
    {
        return $this->getRequest()->getSession()->getOrder()->getPostage();
    }

    /**
     * Return Products total price
     *
     * @return float
     */
    public function getCartTotalPrice()
    {
        return $this->getRequest()->getSession()->getCart()->getTotalAmount();

    }

    public function getCartTotalTaxPrice()
    {
        $taxCountry = $this->getContainer()->get('thelia.taxEngine')->getDeliveryCountry();

        return $this->getCart()->getTaxedAmount($taxCountry, false);
    }

    /**
     * Return the Checkout currency EUR|USD
     *
     * @return Currency
     */
    public function getCheckoutCurrency()
    {
        return $this->getRequest()->getSession()->getCurrency()->getCode();
    }

    /**
     * Return the number of Products in the Cart
     *
     * @return int
     */
    public function getNbArticlesInCart()
    {
        return count($this->getRequest()->getSession()->getCart()->getCartItems());
    }

    /**
     * Return all Coupon given during the Checkout
     *
     * @return array Array of CouponInterface
     */
    public function getCurrentCoupons()
    {
        $couponCodes = $this->getRequest()->getSession()->getConsumedCoupons();

        if (null === $couponCodes) {
            return array();
        }
        $couponFactory = $this->container->get('thelia.coupon.factory');

        $coupons = array();
        foreach ($couponCodes as $couponCode) {
            $coupons[] = $couponFactory->buildCouponFromCode($couponCode);
        }

        return $coupons;
    }

    /**
     * Find one Coupon in the database from its code
     *
     * @param string $code Coupon code
     *
     * @return Coupon
     */
    public function findOneCouponByCode($code)
    {
        $couponQuery = CouponQuery::create();

        return $couponQuery->findOneByCode($code);
    }

    /**
     * Return platform Container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Return platform TranslatorInterface
     *
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->container->get('thelia.translator');
    }

    /**
     * Return platform Parser
     *
     * @return ParserInterface
     */
    public function getParser()
    {
        if ($this->parser == null) {
            $this->parser = $this->container->get('thelia.parser');

            // Define the current back-office template that should be used
            $this->parser->setTemplateDefinition(TemplateHelper::getInstance()->getActiveAdminTemplate());
        }

        return $this->parser;
    }

    /**
     * Return the main currency
     * THe one used to set prices in BackOffice
     *
     * @return string
     */
    public function getMainCurrency()
    {
        return $this->getRequest()->getSession()->getCurrency();
    }

    /**
     * Return request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->container->get('request');
    }

    /**
     * Return Constraint Validator
     *
     * @return ConditionEvaluator
     */
    public function getConditionEvaluator()
    {
        return $this->container->get('thelia.condition.validator');
    }

    /**
     * Return all available currencies
     *
     * @return array of Currency
     */
    public function getAvailableCurrencies()
    {
        $currencies = CurrencyQuery::create();

        return $currencies->find();
    }

    /**
     * Return the event dispatcher,
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->container->get('event_dispatcher');
    }
}
