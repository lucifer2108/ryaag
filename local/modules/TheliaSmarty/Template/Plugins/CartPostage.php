<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TheliaSmarty\Template\Plugins;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Event\Delivery\DeliveryPostageEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\Address;
use Thelia\Model\AddressQuery;
use Thelia\Model\AreaDeliveryModuleQuery;
use Thelia\Model\Base\StateQuery;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;
use Thelia\Model\Customer;
use Thelia\Model\ModuleQuery;
use Thelia\Model\State;
use Thelia\Module\BaseModule;
use Thelia\Module\Exception\DeliveryException;
use TheliaSmarty\Template\AbstractSmartyPlugin;
use TheliaSmarty\Template\SmartyPluginDescriptor;

/**
 * Class CartPostage.
 */
class CartPostage extends AbstractSmartyPlugin
{
    /**
     * @var \Thelia\Core\HttpFoundation\Request The Request
     *
     * @deprecated since 2.3, please use requestStack
     */
    protected $request;

    /** @var RequestStack */
    protected $requestStack;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var ContainerInterface Service Container */
    protected $container;

    /** @var int the id of country */
    protected $countryId;

    /** @var int the id of the cheapest delivery */
    protected $deliveryId;

    /** @var float the postage amount with taxes */
    protected $postage;

    /** @var float the postage tax amount */
    protected $postageTax;

    /** @var string the postage tax rule title */
    protected $postageTaxRuleTitle;

    /** @var bool indicate if customer can change the country */
    protected $isCustomizable = true;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->requestStack = $container->get('request_stack');

        $this->request = $this->getCurrentRequest();

        $this->dispatcher = $container->get('event_dispatcher');
    }

    /**
     * Get postage amount for cart.
     *
     * @param array                     $params   Block parameters
     * @param mixed                     $content  Block content
     * @param \Smarty_Internal_Template $template Template
     * @param bool                      $repeat   Control how many times
     *                                            the block is displayed
     */
    public function postage($params, $content, $template, &$repeat)
    {
        if (!$repeat) {
            return (null !== $this->countryId) ? $content : '';
        }

        $customer = $this->getCurrentRequest()->getSession()->getCustomerUser();
        /** @var Address $address */
        /** @var Country $country */
        [$address, $country, $state] = $this->getDeliveryInformation($customer);

        if (null !== $country) {
            $this->countryId = $country->getId();
            // try to get the cheapest delivery for this country
            $this->getCheapestDelivery($address, $country, $state);
        }

        $template->assign('country_id', $this->countryId);
        $template->assign('delivery_id', $this->deliveryId);
        $template->assign('postage', $this->postage ?: 0.0);
        $template->assign('postage_tax', $this->postageTax ?: 0.0);
        $template->assign('postage_title', $this->postageTaxRuleTitle ?: 0.0);
        $template->assign('is_customizable', $this->isCustomizable);
    }

    /**
     * Retrieve the delivery country for a customer.
     *
     * The rules :
     *  - the country of the delivery address of the customer related to the
     *      cart if it exists
     *  - the country saved in cookie if customer have changed
     *      the default country
     *  - the default country for the shop if it exists
     */
    protected function getDeliveryInformation(Customer $customer = null)
    {
        $address = null;
        // get the selected delivery address
        if (null !== $addressId = $this->getCurrentRequest()->getSession()->getOrder()->getChoosenDeliveryAddress()) {
            if (null !== $address = AddressQuery::create()->findPk($addressId)) {
                $this->isCustomizable = false;

                return [$address, $address->getCountry(), $address->getState()];
            }
        }

        // get country from customer addresses
        if (null !== $customer) {
            $address = AddressQuery::create()
                ->filterByCustomerId($customer->getId())
                ->filterByIsDefault(1)
                ->findOne()
            ;

            if (null !== $address) {
                $this->isCustomizable = false;

                return [$address, $address->getCountry(), $address->getState()];
            }
        }

        // get country from cookie
        $cookieName = ConfigQuery::read('front_cart_country_cookie_name', 'fcccn');
        if ($this->getCurrentRequest()->cookies->has($cookieName)) {
            $cookieVal = $this->getCurrentRequest()->cookies->getInt($cookieName, 0);
            if (0 !== $cookieVal) {
                $country = CountryQuery::create()->findPk($cookieVal);
                if (null !== $country) {
                    return [null, $country, null];
                }
            }
        }

        // get default country for store.
        try {
            $country = Country::getDefaultCountry();

            $firstCountryState = StateQuery::create()
                ->filterByCountryId($country->getId())
                ->filterByVisible(true)
                ->findOne();

            return [null, $country, $firstCountryState];
        } catch (\LogicException $e) {
        }

        return [null, null, null];
    }

    /**
     * Retrieve the cheapest delivery for country.
     *
     * @throws PropelException
     */
    protected function getCheapestDelivery(Address $address = null, Country $country = null, State $state = null): void
    {
        $cart = $this->getCurrentRequest()->getSession()->getSessionCart();

        $deliveryModules = ModuleQuery::create()
            ->filterByActivate(1)
            ->filterByType(BaseModule::DELIVERY_MODULE_TYPE, Criteria::EQUAL)
            ->find();

        $virtual = $cart->isVirtual();

        /** @var \Thelia\Model\Module $deliveryModule */
        foreach ($deliveryModules as $deliveryModule) {
            $areaDeliveryModule = AreaDeliveryModuleQuery::create()
                ->findByCountryAndModule($country, $deliveryModule, $state);
            if (null === $areaDeliveryModule && false === $virtual) {
                continue;
            }

            $moduleInstance = $deliveryModule->getDeliveryModuleInstance($this->container);

            if (true === $virtual
                && false === $moduleInstance->handleVirtualProductDelivery()
            ) {
                continue;
            }

            try {
                $deliveryPostageEvent = new DeliveryPostageEvent($moduleInstance, $cart, $address, $country, $state);
                $this->dispatcher->dispatch(
                    $deliveryPostageEvent,
                    TheliaEvents::MODULE_DELIVERY_GET_POSTAGE
                );

                if ($deliveryPostageEvent->isValidModule()) {
                    $postage = $deliveryPostageEvent->getPostage();

                    if (null === $this->postage || $this->postage > $postage->getAmount()) {
                        $this->postage = $postage->getAmount();
                        $this->postageTax = $postage->getAmountTax();
                        $this->postageTaxRuleTitle = $postage->getTaxRuleTitle();
                        $this->deliveryId = $deliveryModule->getId();
                    }
                }
            } catch (DeliveryException $ex) {
                // Module is not available
            }
        }
    }

    /**
     * Defines the various smarty plugins handled by this class.
     *
     * @return \TheliaSmarty\Template\SmartyPluginDescriptor[] smarty plugin descriptors
     */
    public function getPluginDescriptors()
    {
        return [
            new SmartyPluginDescriptor('block', 'postage', $this, 'postage'),
        ];
    }

    /**
     * @return Request|null
     */
    protected function getCurrentRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
}
