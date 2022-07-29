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

namespace Front\Controller;

use Front\Front;
use Propel\Runtime\Exception\PropelException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Coupon\CouponConsumeEvent;
use Thelia\Core\Event\DefaultActionEvent;
use Thelia\Core\Event\Delivery\DeliveryPostageEvent;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Exception\UnmatchableConditionException;
use Thelia\Form\Definition\FrontForm;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Log\Tlog;
use Thelia\Model\AddressQuery;

/**
 * Class CouponController.
 *
 * @author Guillaume MOREL <gmorel@openstudio.fr>
 */
class CouponController extends BaseFrontController
{
    /**
     * Clear all coupons.
     */
    public function clearAllCouponsAction(EventDispatcherInterface $eventDispatcher): void
    {
        // Dispatch Event to the Action
        $eventDispatcher->dispatch(new DefaultActionEvent(), TheliaEvents::COUPON_CLEAR_ALL);
    }

    /**
     * Coupon consuming.
     */
    public function consumeAction(EventDispatcherInterface $eventDispatcher)
    {
        $this->checkCartNotEmpty($eventDispatcher);

        $message = false;
        $couponCodeForm = $this->createForm(FrontForm::COUPON_CONSUME);

        try {
            $form = $this->validateForm($couponCodeForm, 'post');

            $couponCode = $form->get('coupon-code')->getData();

            if (null === $couponCode || empty($couponCode)) {
                $message = true;
                throw new \Exception(
                    $this->getTranslator()->trans(
                        'Coupon code can\'t be empty',
                        [],
                        Front::MESSAGE_DOMAIN
                    )
                );
            }

            $couponConsumeEvent = new CouponConsumeEvent($couponCode);

            // Dispatch Event to the Action
            $eventDispatcher->dispatch($couponConsumeEvent, TheliaEvents::COUPON_CONSUME);

            /* recalculate postage amount */
            $order = $this->getSession()->getOrder();

            if (null !== $order) {
                $deliveryModule = $order->getModuleRelatedByDeliveryModuleId();
                $deliveryAddress = AddressQuery::create()->findPk($order->getChoosenDeliveryAddress());

                if (null !== $deliveryModule && null !== $deliveryAddress) {
                    $moduleInstance = $deliveryModule->getDeliveryModuleInstance($this->container);

                    $orderEvent = new OrderEvent($order);

                    try {
                        $deliveryPostageEvent = new DeliveryPostageEvent(
                            $moduleInstance,
                            $this->getSession()->getSessionCart($eventDispatcher),
                            $deliveryAddress
                        );

                        $eventDispatcher->dispatch(
                            $deliveryPostageEvent,
                            TheliaEvents::MODULE_DELIVERY_GET_POSTAGE
                        );

                        $postage = $deliveryPostageEvent->getPostage();

                        $orderEvent->setPostage($postage->getAmount());
                        $orderEvent->setPostageTax($postage->getAmountTax());
                        $orderEvent->setPostageTaxRuleTitle($postage->getTaxRuleTitle());

                        $eventDispatcher->dispatch($orderEvent, TheliaEvents::ORDER_SET_POSTAGE);
                    } catch (\Exception $ex) {
                        // The postage has been chosen, but changes dues to coupon causes an exception.
                        // Reset the postage data in the order
                        $orderEvent->setDeliveryModule(0);

                        $eventDispatcher->dispatch($orderEvent, TheliaEvents::ORDER_SET_DELIVERY_MODULE);
                    }
                }
            }

            return $this->generateSuccessRedirect($couponCodeForm);
        } catch (FormValidationException $e) {
            $message = $this->getTranslator()->trans(
                'Please check your coupon code: %message',
                ['%message' => $e->getMessage()],
                Front::MESSAGE_DOMAIN
            );
        } catch (UnmatchableConditionException $e) {
            $message = $this->getTranslator()->trans(
                'You should <a href="%sign">sign in</a> or <a href="%register">register</a> to use this coupon',
                [
                    '%sign' => $this->retrieveUrlFromRouteId('customer.login.view'),
                    '%register' => $this->retrieveUrlFromRouteId('customer.create.view'),
                ],
                Front::MESSAGE_DOMAIN
            );
        } catch (PropelException $e) {
            $this->getParserContext()->setGeneralError($e->getMessage());
        } catch (\Exception $e) {
            $message = $this->getTranslator()->trans(
                'Sorry, an error occurred: %message',
                ['%message' => $e->getMessage()],
                Front::MESSAGE_DOMAIN
            );
        }

        if ($message !== false) {
            Tlog::getInstance()->error(
                sprintf('Error during order delivery process : %s. Exception was %s', $message, $e->getMessage())
            );

            $couponCodeForm->setErrorMessage($message);

            $this->getParserContext()
                ->addForm($couponCodeForm)
                ->setGeneralError($message);
        }

        return $this->generateErrorRedirect($couponCodeForm);
    }
}
