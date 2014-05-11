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

namespace Thelia\Controller\Admin;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Thelia\Condition\ConditionFactory;
use Thelia\Condition\Implementation\ConditionInterface;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Event\Coupon\CouponCreateOrUpdateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Security\AccessManager;
use Thelia\Coupon\CouponFactory;
use Thelia\Coupon\CouponManager;
use Thelia\Condition\ConditionCollection;
use Thelia\Coupon\Type\CouponAbstract;
use Thelia\Coupon\Type\CouponInterface;
use Thelia\Coupon\Type\RemoveXPercent;
use Thelia\Form\CouponCreationForm;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Log\Tlog;
use Thelia\Model\Coupon;
use Thelia\Model\CouponQuery;
use Thelia\Model\LangQuery;
use Thelia\Tools\Rest\ResponseRest;

/**
 * Control View and Action (Model) via Events
 *
 * @package Coupon
 * @author  Guillaume MOREL <gmorel@openstudio.fr>
 *
 */
class CouponController extends BaseAdminController
{
    /**
     * Manage Coupons list display
     *
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function browseAction()
    {
        if (null !== $response = $this->checkAuth(AdminResources::COUPON, [], AccessManager::VIEW)) {
            return $response;
        }

        $args['coupon_order'] = $this->getListOrderFromSession('coupon', 'coupon_order', 'code');

        return $this->render('coupon-list', $args);
    }

    /**
     * Manage Coupons creation display
     *
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function createAction()
    {
        if (null !== $response = $this->checkAuth(AdminResources::COUPON, [], AccessManager::CREATE)) {
            return $response;
        }

        // Parameters given to the template
        $args = [];

        $eventToDispatch = TheliaEvents::COUPON_CREATE;

        if ($this->getRequest()->isMethod('POST')) {
            $this->validateCreateOrUpdateForm(
                $eventToDispatch,
                'created',
                'creation'
            );
        } else {
            // If no input for expirationDate, now + 2 months
            $defaultDate = new \DateTime();
            $args['defaultDate'] = $defaultDate->modify('+2 month')->format($this->getDefaultDateFormat());
        }

        $args['dateFormat'] = $this->getDefaultDateFormat();
        $args['availableCoupons'] = $this->getAvailableCoupons();
        $args['urlAjaxAdminCouponDrawInputs'] = $this->getRoute(
            'admin.coupon.draw.inputs.ajax',
            ['couponServiceId' => 'couponServiceId'],
            Router::ABSOLUTE_URL
        );
        $args['formAction'] = 'admin/coupon/create';

        return $this->render(
            'coupon-create',
            $args
        );
    }

    /**
     * Manage Coupons edition display
     *
     * @param int $couponId Coupon id
     *
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function updateAction($couponId)
    {
        if (null !== $response = $this->checkAuth(AdminResources::COUPON, [], AccessManager::UPDATE)) {
            return $response;
        }

        /** @var Coupon $coupon */
        $coupon = CouponQuery::create()->findPk($couponId);
        if (null === $coupon) {
            return $this->pageNotFound();

        }

        $coupon->setLocale($this->getCurrentEditionLocale());

        /** @var CouponFactory $couponFactory */
        $couponFactory = $this->container->get('thelia.coupon.factory');
        $couponManager = $couponFactory->buildCouponFromModel($coupon);

        // Parameters given to the template
        $args = [];

        $eventToDispatch = TheliaEvents::COUPON_UPDATE;

        // Update
        if ($this->getRequest()->isMethod('POST')) {
            $this->validateCreateOrUpdateForm(
                $eventToDispatch,
                'updated',
                'update',
                $coupon
            );
        } else {
            // Display
            // Prepare the data that will hydrate the form
            /** @var ConditionFactory $conditionFactory */
            $conditionFactory = $this->container->get('thelia.condition.factory');
            $conditions = $conditionFactory->unserializeConditionCollection(
                $coupon->getSerializedConditions()
            );

            $data = [
                'code' => $coupon->getCode(),
                'title' => $coupon->getTitle(),
                'amount' => $coupon->getAmount(),
                'type' => $coupon->getType(),
                'shortDescription' => $coupon->getShortDescription(),
                'description' => $coupon->getDescription(),
                'isEnabled' => $coupon->getIsEnabled(),
                'expirationDate' => $coupon->getExpirationDate($this->getDefaultDateFormat()),
                'isAvailableOnSpecialOffers' => $coupon->getIsAvailableOnSpecialOffers(),
                'isCumulative' => $coupon->getIsCumulative(),
                'isRemovingPostage' => $coupon->getIsRemovingPostage(),
                'maxUsage' => $coupon->getMaxUsage(),
                'conditions' => $conditions,
                'locale' => $this->getCurrentEditionLocale(),
            ];

            $args['conditions'] = $this->cleanConditionForTemplate($conditions);

            // Setup the object form
            $changeForm = new CouponCreationForm($this->getRequest(), 'form', $data);

            // Pass it to the parser
            $this->getParserContext()->addForm($changeForm);
        }

        $args['couponCode'] = $coupon->getCode();
        $args['availableCoupons'] = $this->getAvailableCoupons();
        $args['couponInputsHtml'] = $couponManager->drawBackOfficeInputs();
        $args['urlAjaxAdminCouponDrawInputs'] = $this->getRoute(
            'admin.coupon.draw.inputs.ajax',
            ['couponServiceId' => 'couponServiceId'],
            Router::ABSOLUTE_URL
        );
        $args['availableConditions'] = $this->getAvailableConditions();
        $args['urlAjaxGetConditionInputFromServiceId'] = $this->getRoute(
            'admin.coupon.draw.condition.read.inputs.ajax',
            ['conditionId' => 'conditionId'],
            Router::ABSOLUTE_URL
        );
        $args['urlAjaxGetConditionInputFromConditionInterface'] = $this->getRoute(
            'admin.coupon.draw.condition.update.inputs.ajax',
            [
                'couponId' => $couponId,
                'conditionIndex' => 8888888
            ],
            Router::ABSOLUTE_URL
        );

        $args['urlAjaxSaveConditions'] = $this->getRoute(
            'admin.coupon.condition.save',
            ['couponId' => $couponId],
            Router::ABSOLUTE_URL
        );
        $args['urlAjaxDeleteConditions'] = $this->getRoute(
            'admin.coupon.condition.delete',
            [
                'couponId' => $couponId,
                'conditionIndex' => 8888888
            ],
            Router::ABSOLUTE_URL
        );
        $args['urlAjaxGetConditionSummaries'] = $this->getRoute(
            'admin.coupon.draw.condition.summaries.ajax',
            ['couponId' => $couponId],
            Router::ABSOLUTE_URL
        );

        $args['formAction'] = 'admin/coupon/update/' . $couponId;

        $args['dateFormat'] = $this->getDefaultDateFormat();

        return $this->render('coupon-update', $args);
    }

    /**
     * Manage Coupons read display
     *
     * @param string $conditionId Condition service id
     *
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function getConditionEmptyInputAjaxAction($conditionId)
    {
        if (null !== $response = $this->checkAuth(AdminResources::COUPON, [], AccessManager::VIEW)) {
            return $response;
        }

        $this->checkXmlHttpRequest();

        if (! empty($conditionId)) {

            /** @var ConditionFactory $conditionFactory */
            $conditionFactory = $this->container->get('thelia.condition.factory');
            $inputs = $conditionFactory->getInputsFromServiceId($conditionId);

            if (!$this->container->has($conditionId)) {
                return false;
            }

            if ($inputs === null) {
                return $this->pageNotFound();
            }

            /** @var ConditionInterface $condition */
            $condition = $this->container->get($conditionId);

            $html      = $condition->drawBackOfficeInputs();
            $serviceId = $condition->getServiceId();
        } else {
            $html = '';
            $serviceId = '';
        }

        return $this->render(
            'coupon/condition-input-ajax',
            [
                'inputsDrawn' => $html,
                'conditionServiceId' => $serviceId,
                'conditionIndex' => ''
            ]
        );
    }

    /**
     * Manage Coupons read display
     *
     * @param int $couponId       Coupon id being updated
     * @param int $conditionIndex Coupon Condition position in the collection
     *
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function getConditionToUpdateInputAjaxAction($couponId, $conditionIndex)
    {
        if (null !== $response = $this->checkAuth(AdminResources::COUPON, [], AccessManager::VIEW)) {
            return $response;
        }

        $this->checkXmlHttpRequest();

        $search = CouponQuery::create();
        /** @var Coupon $coupon */
        $coupon = $search->findOneById($couponId);
        if (!$coupon) {
            return $this->pageNotFound();
        }

        /** @var CouponFactory $couponFactory */
        $couponFactory = $this->container->get('thelia.coupon.factory');
        $couponManager = $couponFactory->buildCouponFromModel($coupon);

        if (!$couponManager instanceof CouponInterface) {
            return $this->pageNotFound();
        }

        $conditions = $couponManager->getConditions();
        if (!isset($conditions[$conditionIndex])) {
            return $this->pageNotFound();
        }
        /** @var ConditionInterface $condition */
        $condition = $conditions[$conditionIndex];

        /** @var ConditionFactory $conditionFactory */
        $conditionFactory = $this->container->get('thelia.condition.factory');
        $inputs = $conditionFactory->getInputsFromConditionInterface($condition);

        if ($inputs === null) {
            return $this->pageNotFound();
        }

        return $this->render(
            'coupon/condition-input-ajax', [
                'inputsDrawn' => $condition->drawBackOfficeInputs(),
                'conditionServiceId' => $condition->getServiceId(),
                'conditionIndex' => $conditionIndex,
            ]
        );
    }

    /**
     * Manage Coupons read display
     *
     * @param int $couponId Coupon id
     *
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function saveConditionsAction($couponId)
    {
        if (null !== $response = $this->checkAuth(AdminResources::COUPON, [], AccessManager::UPDATE)) {
            return $response;
        }

        $this->checkXmlHttpRequest();

        $search = CouponQuery::create();
        /** @var Coupon $coupon */
        $coupon = $search->findOneById($couponId);
        if (!$coupon) {
            return $this->pageNotFound();
        }

        $conditionToSave = $this->buildConditionFromRequest();

        /** @var CouponFactory $couponFactory */
        $couponFactory = $this->container->get('thelia.coupon.factory');
        $couponManager = $couponFactory->buildCouponFromModel($coupon);

        if (!$couponManager instanceof CouponInterface) {
            return $this->pageNotFound();
        }

        $conditions = $couponManager->getConditions();
        $conditionIndex = $this->getRequest()->request->get('conditionIndex');
        if ($conditionIndex >= 0) {
            // Update mode
            $conditions[$conditionIndex] = $conditionToSave;
        } else {
            // Insert mode
            $conditions[] = $conditionToSave;
        }
        $couponManager->setConditions($conditions);

        $this->manageConditionUpdate($coupon, $conditions);

        return new Response();
    }

    /**
     * Manage Coupons condition deleteion
     *
     * @param int $couponId       Coupon id
     * @param int $conditionIndex Coupon condition index in the collection
     *
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function deleteConditionsAction($couponId, $conditionIndex)
    {
        if (null !== $response = $this->checkAuth(AdminResources::COUPON, [], AccessManager::UPDATE)) {
            return $response;
        }

        $this->checkXmlHttpRequest();

        $search = CouponQuery::create();
        /** @var Coupon $coupon */
        $coupon = $search->findOneById($couponId);
        if (!$coupon) {
            return $this->pageNotFound();
        }

        /** @var CouponFactory $couponFactory */
        $couponFactory = $this->container->get('thelia.coupon.factory');
        $couponManager = $couponFactory->buildCouponFromModel($coupon);

        if (!$couponManager instanceof CouponInterface) {
            return $this->pageNotFound();
        }

        $conditions = $couponManager->getConditions();
        unset($conditions[$conditionIndex]);
        $couponManager->setConditions($conditions);

        $this->manageConditionUpdate($coupon, $conditions);

        return new Response();
    }

    /**
     * Log error message
     *
     * @param string     $action  Creation|Update|Delete
     * @param string     $message Message to log
     * @param \Exception $e       Exception to log
     *
     * @return $this
     */
    protected function logError($action, $message, $e)
    {
        Tlog::getInstance()->error(
            sprintf(
                'Error during Coupon ' . $action . ' process : %s. Exception was %s',
                $message,
                $e->getMessage()
            )
        );

        return $this;
    }

    /**
     * Validate the CreateOrUpdate form
     *
     * @param string $eventToDispatch Event which will activate actions
     * @param string $log             created|edited
     * @param string $action          creation|edition
     * @param Coupon $model           Model if in update mode
     *
     * @return $this
     */
    protected function validateCreateOrUpdateForm($eventToDispatch, $log, $action, Coupon $model = null)
    {
        // Create the form from the request
        $couponForm = new CouponCreationForm($this->getRequest());

        $message = false;
        try {
            // Check the form against conditions violations
            $form = $this->validateForm($couponForm, 'POST');

            $couponEvent = $this->feedCouponCreateOrUpdateEvent($form, $model);

            // Dispatch Event to the Action
            $this->dispatch(
                $eventToDispatch,
                $couponEvent
            );

            $this->adminLogAppend(
                AdminResources::COUPON, AccessManager::UPDATE,
                sprintf(
                    'Coupon %s (ID ) ' . $log,
                    $couponEvent->getTitle(),
                    $couponEvent->getCouponModel()->getId()
                )
            );

            if ($this->getRequest()->get('save_mode') == 'stay') {
                $this->redirect(
                    str_replace(
                        '{id}',
                        $couponEvent->getCouponModel()->getId(),
                        $couponForm->getSuccessUrl()
                    )
                );

                exit();
            }

            // Redirect to the success URL
            $this->redirectToRoute('admin.coupon.list');

        } catch (FormValidationException $ex) {
            // Invalid data entered
            $message = $this->createStandardFormValidationErrorMessage($ex);

        } catch (\Exception $ex) {
            // Any other error
            $message = $this->getTranslator()->trans('Sorry, an error occurred: %err', ['%err' => $ex->getMessage()]);

            $this->logError($action, $message, $ex);
        }

        if ($message !== false) {
            // Mark the form as with error
            $couponForm->setErrorMessage($message);

            // Send the form and the error to the parser
            $this->getParserContext()
                ->addForm($couponForm)
                ->setGeneralError($message);
        }

        return $this;
    }

    /**
     * Get all available conditions
     *
     * @return array
     */
    protected function getAvailableConditions()
    {
        /** @var CouponManager $couponManager */
        $couponManager = $this->container->get('thelia.coupon.manager');
        $availableConditions = $couponManager->getAvailableConditions();
        $cleanedConditions = [];
        /** @var ConditionInterface $availableCondition */
        foreach ($availableConditions as $availableCondition) {
            $condition = [];
            $condition['serviceId'] = $availableCondition->getServiceId();
            $condition['name'] = $availableCondition->getName();
            $condition['toolTip'] = $availableCondition->getToolTip();
            $cleanedConditions[] = $condition;
        }

        return $cleanedConditions;
    }

    /**
     * Get all available coupons
     *
     * @return array
     */
    protected function getAvailableCoupons()
    {
        /** @var CouponManager $couponManager */
        $couponManager = $this->container->get('thelia.coupon.manager');
        $availableCoupons = $couponManager->getAvailableCoupons();
        $cleanedCoupons = [];
        /** @var CouponInterface $availableCoupon */
        foreach ($availableCoupons as $availableCoupon) {
            $condition = [];
            $condition['serviceId'] = $availableCoupon->getServiceId();
            $condition['name'] = $availableCoupon->getName();
            $condition['toolTip'] = $availableCoupon->getToolTip();
            $condition['inputName'] = $availableCoupon->getInputName();
            $cleanedCoupons[] = $condition;
        }

        return $cleanedCoupons;
    }

    /**
     * Clean condition for template
     *
     * @param ConditionCollection $conditions Condition collection
     *
     * @return array
     */
    protected function cleanConditionForTemplate(ConditionCollection $conditions)
    {
        $cleanedConditions = [];
        /** @var $condition ConditionInterface */
        foreach ($conditions as $index => $condition) {
            $temp = [
                'serviceId' => $condition->getServiceId(),
                'index' => $index,
                'name' => $condition->getName(),
                'toolTip' => $condition->getToolTip(),
                'summary' => $condition->getSummary(),
                'validators' => $condition->getValidators()
            ];
            $cleanedConditions[] = $temp;
        }

        return $cleanedConditions;
    }

    /**
     * Draw the input displayed in the BackOffice
     * allowing Admin to set its Coupon effect
     *
     * @param string $couponServiceId Coupon service id
     *
     * @return ResponseRest
     */
    public function getBackOfficeInputsAjaxAction($couponServiceId)
    {
        if (null !== $response = $this->checkAuth(AdminResources::COUPON, [], AccessManager::VIEW)) {
            return $response;
        }

        $this->checkXmlHttpRequest();

        /** @var CouponInterface $coupon */
        $couponManager = $this->container->get($couponServiceId);

        if (!$couponManager instanceof CouponInterface) {
            $this->pageNotFound();
        }

        $response = new ResponseRest($couponManager->drawBackOfficeInputs());

        return $response;
    }

    /**
     * Draw the input displayed in the BackOffice
     * allowing Admin to set its Coupon effect
     *
     * @param int $couponId Coupon id
     *
     * @return ResponseRest
     */
    public function getBackOfficeConditionSummariesAjaxAction($couponId)
    {
        if (null !== $response = $this->checkAuth(AdminResources::COUPON, [], AccessManager::VIEW)) {
            return $response;
        }

        $this->checkXmlHttpRequest();

        /** @var Coupon $coupon */
        $coupon = CouponQuery::create()->findPk($couponId);
        if (null === $coupon) {
            return $this->pageNotFound();

        }

        /** @var CouponFactory $couponFactory */
        $couponFactory = $this->container->get('thelia.coupon.factory');
        $couponManager = $couponFactory->buildCouponFromModel($coupon);

        if (!$couponManager instanceof CouponInterface) {
            return $this->pageNotFound();
        }

        $args = [];
        $args['conditions'] = $this->cleanConditionForTemplate($couponManager->getConditions());

        return $this->render('coupon/conditions', $args);

    }

    /**
     * Add percentage logic if found in the Coupon post data
     *
     * @param array $effects            Effect parameters to populate
     * @param array $extendedInputNames Extended Inputs to manage
     *
     * @return array Populated effect with percentage
     */
    protected function addExtendedLogic(array $effects, array $extendedInputNames)
    {
        /** @var Request $request */
        $request = $this->container->get('request');
        $postData = $request->request;
        // Validate quantity input

        if ($postData->has(RemoveXPercent::INPUT_EXTENDED__NAME)) {
            $extentedPostData = $postData->get(RemoveXPercent::INPUT_EXTENDED__NAME);

            foreach ($extendedInputNames as $extendedInputName) {
                if (isset($extentedPostData[$extendedInputName])) {
                    $inputValue = $extentedPostData[$extendedInputName];
                    $effects[$extendedInputName] = $inputValue;
                }
            }
        }

        return $effects;
    }

    /**
     * Feed the Coupon Create or Update event with the User inputs
     *
     * @param Form   $form  Form containing user data
     * @param Coupon $model Model if in update mode
     *
     * @return CouponCreateOrUpdateEvent
     */
    protected function feedCouponCreateOrUpdateEvent(Form $form, Coupon $model = null)
    {
        // Get the form field values
        $data = $form->getData();
        $serviceId = $data['type'];
        /** @var CouponInterface $couponManager */
        $couponManager = $this->container->get($serviceId);
        $effects = [CouponAbstract::INPUT_AMOUNT_NAME => $data[CouponAbstract::INPUT_AMOUNT_NAME]];
        $effects = $this->addExtendedLogic($effects, $couponManager->getExtendedInputs());

        $couponEvent = new CouponCreateOrUpdateEvent(
            $data['code'],
            $serviceId,
            $data['title'],
            $effects,
            $data['shortDescription'],
            $data['description'],
            $data['isEnabled'],
            \DateTime::createFromFormat($this->getDefaultDateFormat(), $data['expirationDate']),
            $data['isAvailableOnSpecialOffers'],
            $data['isCumulative'],
            $data['isRemovingPostage'],
            $data['maxUsage'],
            $data['locale']
        );

        // If Update mode
        if (isset($model)) {
            $couponEvent->setCouponModel($model);
        }

        return $couponEvent;
    }

    /**
     * Build ConditionInterface from request
     *
     * @return ConditionInterface
     */
    protected function buildConditionFromRequest()
    {
        $request = $this->getRequest();
        $post = $request->request->getIterator();
        $serviceId = $request->request->get('categoryCondition');
        $operators = [];
        $values = [];
        foreach ($post as $key => $input) {
            if (isset($input['operator']) && isset($input['value'])) {
                $operators[$key] = $input['operator'];
                $values[$key] = $input['value'];
            }
        }

        /** @var ConditionFactory $conditionFactory */
        $conditionFactory = $this->container->get('thelia.condition.factory');
        $conditionToSave = $conditionFactory->build($serviceId, $operators, $values);

        return $conditionToSave;
    }

    /**
     * Manage how a Condition is updated
     *
     * @param Coupon              $coupon     Coupon Model
     * @param ConditionCollection $conditions Condition collection
     */
    protected function manageConditionUpdate(Coupon $coupon, ConditionCollection $conditions)
    {
        $couponEvent = new CouponCreateOrUpdateEvent(
            $coupon->getCode(),
            $coupon->getType(),
            $coupon->getTitle(),
            $coupon->getEffects(),
            $coupon->getShortDescription(),
            $coupon->getDescription(),
            $coupon->getIsEnabled(),
            $coupon->getExpirationDate(),
            $coupon->getIsAvailableOnSpecialOffers(),
            $coupon->getIsCumulative(),
            $coupon->getIsRemovingPostage(),
            $coupon->getMaxUsage(),
            $coupon->getLocale()
        );
        $couponEvent->setCouponModel($coupon);
        $couponEvent->setConditions($conditions);

        $eventToDispatch = TheliaEvents::COUPON_CONDITION_UPDATE;
        // Dispatch Event to the Action
        $this->dispatch(
            $eventToDispatch,
            $couponEvent
        );

        $this->adminLogAppend(
            AdminResources::COUPON, AccessManager::UPDATE,
            sprintf(
                'Coupon %s (ID %s) conditions updated',
                $couponEvent->getCouponModel()->getTitle(),
                $couponEvent->getCouponModel()->getType()
            )
        );
    }

    protected function getDefaultDateFormat()
    {
        return LangQuery::create()->findOneByByDefault(true)->getDateFormat();
    }
}
