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

namespace Thelia\Action;

use Propel\Runtime\Propel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Condition\ConditionCollection;
use Thelia\Condition\ConditionFactory;
use Thelia\Condition\Implementation\ConditionInterface;
use Thelia\Core\Event\Coupon\CouponConsumeEvent;
use Thelia\Core\Event\Coupon\CouponCreateOrUpdateEvent;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Coupon\CouponFactory;
use Thelia\Coupon\CouponManager;
use Thelia\Coupon\Type\CouponInterface;
use Thelia\Model\Coupon as CouponModel;
use Thelia\Model\CouponCountry;
use Thelia\Model\CouponCountryQuery;
use Thelia\Model\CouponModule;
use Thelia\Model\CouponModuleQuery;
use Thelia\Model\CouponQuery;
use Thelia\Model\Map\OrderCouponTableMap;
use Thelia\Model\OrderCoupon;
use Thelia\Model\OrderCouponCountry;
use Thelia\Model\OrderCouponModule;

/**
 * Process Coupon Events
 *
 * @package Coupon
 * @author  Guillaume MOREL <gmorel@openstudio.fr>, Franck Allimant <franck@cqfdev.fr>
 *
 */
class Coupon extends BaseAction implements EventSubscriberInterface
{
    /**
     * @var \Thelia\Core\HttpFoundation\Request
     */
    protected $request;

    /** @var CouponFactory $couponFactory */
    protected $couponFactory;

    /** @var CouponManager $couponManager */
    protected $couponManager;

    /** @var ConditionInterface $noConditionRule */
    protected $noConditionRule;

    /** @var ConditionFactory $conditionFactory */
    protected $conditionFactory;

    public function __construct(Request $request,
        CouponFactory $couponFactory, CouponManager $couponManager,
        ConditionInterface $noConditionRule, ConditionFactory $conditionFactory)
    {
        $this->request = $request;
        $this->couponFactory = $couponFactory;
        $this->couponManager = $couponManager;
        $this->noConditionRule = $noConditionRule;
        $this->conditionFactory = $conditionFactory;
    }

     /**
     * Occurring when a Coupon is about to be created
     *
     * @param CouponCreateOrUpdateEvent $event Event creation or update Coupon
     */
    public function create(CouponCreateOrUpdateEvent $event)
    {
        $coupon = new CouponModel();

        $this->createOrUpdate($coupon, $event);
    }

    /**
     * Occurring when a Coupon is about to be updated
     *
     * @param CouponCreateOrUpdateEvent $event Event creation or update Coupon
     */
    public function update(CouponCreateOrUpdateEvent $event)
    {
        $coupon = $event->getCouponModel();

        $this->createOrUpdate($coupon, $event);
    }

    /**
     * Occurring when a Coupon condition is about to be updated
     *
     * @param CouponCreateOrUpdateEvent $event Event creation or update Coupon condition
     */
    public function updateCondition(CouponCreateOrUpdateEvent $event)
    {
        $modelCoupon = $event->getCouponModel();

        $this->createOrUpdateCondition($modelCoupon, $event);
    }

    /**
     * Occurring when a Coupon condition is about to be consumed
     *
     * @param CouponConsumeEvent $event Event consuming Coupon
     */
    public function consume(CouponConsumeEvent $event)
    {
        $totalDiscount = 0;
        $isValid = false;

        /** @var CouponInterface $coupon */
        $coupon = $this->couponFactory->buildCouponFromCode($event->getCode());

        if ($coupon) {

            $isValid = $coupon->isMatching();

            if ($isValid) {
                $consumedCoupons = $this->request->getSession()->getConsumedCoupons();

                if (!isset($consumedCoupons) || !$consumedCoupons) {
                    $consumedCoupons = array();
                }

                if (!isset($consumedCoupons[$event->getCode()])) {

                    // Prevent accumulation of the same Coupon on a Checkout
                    $consumedCoupons[$event->getCode()] = $event->getCode();

                    $this->request->getSession()->setConsumedCoupons($consumedCoupons);

                    $totalDiscount = $this->couponManager->getDiscount();

                    $this->request
                        ->getSession()
                        ->getCart()
                        ->setDiscount($totalDiscount)
                        ->save();
                    $this->request
                        ->getSession()
                        ->getOrder()
                        ->setDiscount($totalDiscount)
                    ;
                }
            }
        }

        $event->setIsValid($isValid);
        $event->setDiscount($totalDiscount);
    }

    public function updateOrderDiscount(/** @noinspection PhpUnusedParameterInspection */ $event)
    {
        $discount = $this->couponManager->getDiscount();

        $this->request
            ->getSession()
            ->getCart()
            ->setDiscount($discount)
            ->save();

        $this->request
            ->getSession()
            ->getOrder()
            ->setDiscount($discount);
    }

    /**
     * Call the Model and delegate the create or delete action
     * Feed the Event with the updated model
     *
     * @param CouponModel               $coupon Model to save
     * @param CouponCreateOrUpdateEvent $event  Event containing data
     */
    protected function createOrUpdate(CouponModel $coupon, CouponCreateOrUpdateEvent $event)
    {
        $coupon->setDispatcher($event->getDispatcher());

        // Set default condition if none found
        /** @var ConditionInterface $noConditionRule */
        $noConditionRule = $this->noConditionRule;
        /** @var ConditionFactory $conditionFactory */
        $conditionFactory = $this->conditionFactory;
        $couponRuleCollection = new ConditionCollection();
        $couponRuleCollection[] = $noConditionRule;
        $defaultSerializedRule = $conditionFactory->serializeConditionCollection(
            $couponRuleCollection
        );

        $coupon->createOrUpdate(
            $event->getCode(),
            $event->getTitle(),
            $event->getEffects(),
            $event->getServiceId(),
            $event->isRemovingPostage(),
            $event->getShortDescription(),
            $event->getDescription(),
            $event->isEnabled(),
            $event->getExpirationDate(),
            $event->isAvailableOnSpecialOffers(),
            $event->isCumulative(),
            $event->getMaxUsage(),
            $defaultSerializedRule,
            $event->getLocale(),
            $event->getFreeShippingForCountries(),
            $event->getFreeShippingForMethods(),
            $event->getPerCustomerUsageCount()
        );

        $event->setCouponModel($coupon);
    }

    /**
     * Call the Model and delegate the create or delete action
     * Feed the Event with the updated model
     *
     * @param CouponModel               $coupon Model to save
     * @param CouponCreateOrUpdateEvent $event  Event containing data
     */
    protected function createOrUpdateCondition(CouponModel $coupon, CouponCreateOrUpdateEvent $event)
    {
        $coupon->setDispatcher($event->getDispatcher());

        /** @var ConditionFactory $conditionFactory */
        $conditionFactory = $this->conditionFactory;

        $coupon->createOrUpdateConditions(
            $conditionFactory->serializeConditionCollection($event->getConditions()),
            $event->getLocale()
        );

        $event->setCouponModel($coupon);
    }

    /**
     * @param \Thelia\Core\Event\Order\OrderEvent $event
     */
    public function testFreePostage(OrderEvent $event)
    {
        $order = $event->getOrder();

        if ($this->couponManager->isCouponRemovingPostage($order)) {

            $order->setPostage(0);

            $event->setOrder($order);

            $event->stopPropagation();
        }
    }

    /**
     * @param \Thelia\Core\Event\Order\OrderEvent $event
     *
     * @throws \Exception if something goes wrong.
     */
    public function afterOrder(OrderEvent $event)
    {
        $consumedCoupons = $this->request->getSession()->getConsumedCoupons();

        if (is_array($consumedCoupons)) {

            $con = Propel::getWriteConnection(OrderCouponTableMap::DATABASE_NAME);
            $con->beginTransaction();

            try {
                foreach ($consumedCoupons as $couponCode) {
                    $couponQuery = CouponQuery::create();
                    $couponModel = $couponQuery->findOneByCode($couponCode);
                    $couponModel->setLocale($this->request->getSession()->getLang()->getLocale());

                    /* decrease coupon quantity */
                    $this->couponManager->decrementQuantity($couponModel, $event->getOrder()->getCustomerId());

                    /* memorize coupon */
                    $orderCoupon = new OrderCoupon();
                    $orderCoupon->setOrder($event->getOrder())
                        ->setCode($couponModel->getCode())
                        ->setType($couponModel->getType())
                        ->setAmount($couponModel->getAmount())

                        ->setTitle($couponModel->getTitle())
                        ->setShortDescription($couponModel->getShortDescription())
                        ->setDescription($couponModel->getDescription())

                        ->setExpirationDate($couponModel->getExpirationDate())
                        ->setIsCumulative($couponModel->getIsCumulative())
                        ->setIsRemovingPostage($couponModel->getIsRemovingPostage())
                        ->setIsAvailableOnSpecialOffers($couponModel->getIsAvailableOnSpecialOffers())
                        ->setSerializedConditions($couponModel->getSerializedConditions())
                        ->setPerCustomerUsageCount($couponModel->getPerCustomerUsageCount())
                    ;
                    $orderCoupon->save();

                    // Copy order coupon free shipping data for countries and modules
                    $couponCountries = CouponCountryQuery::create()->filterByCouponId($couponModel->getId())->find();

                    /** @var CouponCountry $couponCountry */
                    foreach ($couponCountries as $couponCountry) {
                        $occ = new OrderCouponCountry();

                        $occ
                            ->setCouponId($orderCoupon->getId())
                            ->setCountryId($couponCountry->getCountryId())
                            ->save();
                        ;
                    }

                    $couponModules = CouponModuleQuery::create()->filterByCouponId($couponModel->getId())->find();

                    /** @var CouponModule $couponModule */
                    foreach ($couponModules as $couponModule) {
                        $ocm = new OrderCouponModule();

                        $ocm
                            ->setCouponId($orderCoupon->getId())
                            ->setModuleId($couponModule->getModuleId())
                            ->save();
                        ;
                    }
                }

                $con->commit();
            } catch (\Exception  $ex) {
                $con->rollBack();

                throw($ex);
            }
        }

        $this->request->getSession()->setConsumedCoupons(array());
    }

    /**
     * Returns an array of event names this subscriber listens to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            TheliaEvents::COUPON_CREATE => array("create", 128),
            TheliaEvents::COUPON_UPDATE => array("update", 128),
            TheliaEvents::COUPON_CONSUME => array("consume", 128),
            TheliaEvents::COUPON_CONDITION_UPDATE => array("updateCondition", 128),
            TheliaEvents::ORDER_SET_POSTAGE => array("testFreePostage", 132),
            TheliaEvents::ORDER_BEFORE_PAYMENT => array("afterOrder", 128),
            TheliaEvents::CART_ADDITEM => array("updateOrderDiscount", 10),
            TheliaEvents::CART_UPDATEITEM => array("updateOrderDiscount", 10),
            TheliaEvents::CART_DELETEITEM => array("updateOrderDiscount", 10),
        );
    }
}
