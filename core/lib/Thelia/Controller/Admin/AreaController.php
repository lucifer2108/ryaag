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

use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Event\Area\AreaAddCountryEvent;
use Thelia\Core\Event\Area\AreaCreateEvent;
use Thelia\Core\Event\Area\AreaDeleteEvent;
use Thelia\Core\Event\Area\AreaRemoveCountryEvent;
use Thelia\Core\Event\Area\AreaUpdateEvent;
use Thelia\Core\Event\Area\AreaUpdatePostageEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Security\AccessManager;
use Thelia\Form\Area\AreaCountryForm;
use Thelia\Form\Area\AreaCreateForm;
use Thelia\Form\Area\AreaModificationForm;
use Thelia\Form\Area\AreaPostageForm;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\AreaQuery;

/**
 * Class AreaController
 * @package Thelia\Controller\Admin
 * @author Manuel Raynaud <mraynaud@openstudio.fr>
 */
class AreaController extends AbstractCrudController
{

    public function __construct()
    {
        parent::__construct(
            'area',
            null,
            null,

            AdminResources::AREA,

            TheliaEvents::AREA_CREATE,
            TheliaEvents::AREA_UPDATE,
            TheliaEvents::AREA_DELETE
        );
    }

    protected function getAreaId()
    {
        return $this->getRequest()->get('area_id', 0);
    }

    /**
     * Return the creation form for this object
     */
    protected function getCreationForm()
    {
        return new AreaCreateForm($this->getRequest());
    }

    /**
     * Return the update form for this object
     */
    protected function getUpdateForm()
    {
        return new AreaModificationForm($this->getRequest());
    }

    /**
     * Hydrate the update form for this object, before passing it to the update template
     *
     * @param unknown $object
     */
    protected function hydrateObjectForm($object)
    {
        $data = array(
            'name' => $object->getName()
        );

        return new AreaModificationForm($this->getRequest(), 'form', $data);
    }

    /**
     * Creates the creation event with the provided form data
     *
     * @param unknown $formData
     *
     * @return \Thelia\Core\Event\Area\AreaCreateEvent
     */
    protected function getCreationEvent($formData)
    {
        $event = new AreaCreateEvent();

        return $this->hydrateEvent($event, $formData);
    }

    /**
     * Creates the update event with the provided form data
     *
     * @param unknown $formData
     */
    protected function getUpdateEvent($formData)
    {
        $event = new AreaUpdateEvent();

        return $this->hydrateEvent($event, $formData);
    }

    private function hydrateEvent($event, $formData)
    {
        $event->setAreaName($formData['name']);

        return $event;
    }

    /**
     * Creates the delete event with the provided form data
     */
    protected function getDeleteEvent()
    {
        return new AreaDeleteEvent($this->getAreaId());
    }

    /**
     * Return true if the event contains the object, e.g. the action has updated the object in the event.
     *
     * @param \Thelia\Core\Event\Area\AreaEvent $event
     */
    protected function eventContainsObject($event)
    {
        return $event->hasArea();
    }

    /**
     * Get the created object from an event.
     *
     * @param \Thelia\Core\Event\Area\AreaEvent $event
     */
    protected function getObjectFromEvent($event)
    {
        return $event->getArea();
    }

    /**
     * Load an existing object from the database
     */
    protected function getExistingObject()
    {
        return AreaQuery::create()->findPk($this->getAreaId());
    }

    /**
     * Returns the object label form the object event (name, title, etc.)
     *
     * @param \Thelia\Model\Area $object
     */
    protected function getObjectLabel($object)
    {
        return $object->getName();
    }

    /**
     * Returns the object ID from the object
     *
     * @param \Thelia\Model\Area $object
     */
    protected function getObjectId($object)
    {
        return $object->getId();
    }

    /**
     * Render the main list template
     *
     * @param unknown $currentOrder, if any, null otherwise.
     */
    protected function renderListTemplate($currentOrder)
    {
        return $this->render("shipping-configuration");
    }

    /**
     * Render the edition template
     */
    protected function renderEditionTemplate()
    {
        return $this->render('shipping-configuration-edit',array(
            'area_id' => $this->getAreaId()
        ));
    }

    /**
     * Redirect to the edition template
     */
    protected function redirectToEditionTemplate()
    {
        $this->redirectToRoute('admin.configuration.shipping-configuration.update.view', array(), array(
                "area_id" => $this->getAreaId()
            )
        );
    }

    /**
     * Redirect to the list template
     */
    protected function redirectToListTemplate()
    {
        $this->redirectToRoute('admin.configuration.shipping-configuration.default');
    }

    /**
     * add a country to a define area
     */
    public function addCountry()
    {
        // Check current user authorization
        if (null !== $response = $this->checkAuth($this->resourceCode, array(), AccessManager::UPDATE)) return $response;

        $areaCountryForm = new AreaCountryForm($this->getRequest());
        $error_msg = null;
        try {

            $form = $this->validateForm($areaCountryForm);

            $event = new AreaAddCountryEvent($form->get('area_id')->getData(), $form->get('country_id')->getData());

            $this->dispatch(TheliaEvents::AREA_ADD_COUNTRY, $event);

            if (! $this->eventContainsObject($event))
                throw new \LogicException(
                    $this->getTranslator()->trans("No %obj was updated.", array('%obj', $this->objectName)));

            // Log object modification
            if (null !== $changedObject = $this->getObjectFromEvent($event)) {
                $this->adminLogAppend($this->resourceCode, AccessManager::UPDATE, sprintf("%s %s (ID %s) modified, new country added", ucfirst($this->objectName), $this->getObjectLabel($changedObject), $this->getObjectId($changedObject)));
            }

            // Redirect to the success URL
            $this->redirect($areaCountryForm->getSuccessUrl());

        } catch (FormValidationException $ex) {
            // Form cannot be validated
            $error_msg = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            // Any other error
            $error_msg = $ex->getMessage();
        }

        $this->setupFormErrorContext(
            $this->getTranslator()->trans("%obj modification", array('%obj' => $this->objectName)), $error_msg, $areaCountryForm);

        // At this point, the form has errors, and should be redisplayed.
        return $this->renderEditionTemplate();
    }

    public function removeCountry()
    {
        // Check current user authorization
        if (null !== $response = $this->checkAuth($this->resourceCode, array(), AccessManager::UPDATE)) return $response;
        $request = $this->getRequest();
        $removeCountryEvent = new AreaRemoveCountryEvent($request->request->get('area_id', 0), $request->request->get('country_id', 0));

        $this->dispatch(TheliaEvents::AREA_REMOVE_COUNTRY, $removeCountryEvent);

        $this->redirectToEditionTemplate();
    }

    public function updatePostageAction()
    {
        if (null !== $response = $this->checkAuth($this->resourceCode, array(), AccessManager::UPDATE)) return $response;

        $areaUpdateForm = new AreaPostageForm($this->getRequest());
        $error_msg = null;

        try {
            $form = $this->validateForm($areaUpdateForm);

            $event = new AreaUpdatePostageEvent($form->get('area_id')->getData());
            $event->setPostage($form->get('postage')->getData());

            $this->dispatch(TheliaEvents::AREA_POSTAGE_UPDATE, $event);

            if (! $this->eventContainsObject($event))
                throw new \LogicException(
                    $this->getTranslator()->trans("No %obj was updated.", array('%obj', $this->objectName)));

            // Log object modification
            if (null !== $changedObject = $this->getObjectFromEvent($event)) {
                $this->adminLogAppend($this->resourceCode, AccessManager::UPDATE, sprintf("%s %s (ID %s) modified, country remove", ucfirst($this->objectName), $this->getObjectLabel($changedObject), $this->getObjectId($changedObject)));
            }

            // Redirect to the success URL
            $this->redirect($areaUpdateForm->getSuccessUrl());
        } catch (FormValidationException $ex) {
            // Form cannot be validated
            $error_msg = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            // Any other error
            $error_msg = $ex->getMessage();
        }

        $this->setupFormErrorContext(
            $this->getTranslator()->trans("%obj modification", array('%obj' => $this->objectName)), $error_msg, $areaUpdateForm);

        // At this point, the form has errors, and should be redisplayed.
        return $this->renderEditionTemplate();
    }
}
