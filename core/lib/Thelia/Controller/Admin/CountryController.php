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
use Thelia\Core\Event\Country\CountryCreateEvent;
use Thelia\Core\Event\Country\CountryDeleteEvent;
use Thelia\Core\Event\Country\CountryToggleDefaultEvent;
use Thelia\Core\Event\Country\CountryUpdateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Security\AccessManager;
use Thelia\Form\CountryCreationForm;
use Thelia\Form\CountryModificationForm;
use Thelia\Log\Tlog;
use Thelia\Model\CountryQuery;

/**
 * Class CustomerController
 * @package Thelia\Controller\Admin
 * @author Manuel Raynaud <mraynaud@openstudio.fr>
 */
class CountryController extends AbstractCrudController
{

    public function __construct()
    {
        parent::__construct(
            'country',
            'manual',
            'country_order',

            AdminResources::COUNTRY,

            TheliaEvents::COUNTRY_CREATE,
            TheliaEvents::COUNTRY_UPDATE,
            TheliaEvents::COUNTRY_DELETE
        );
    }

    /**
     * Return the creation form for this object
     */
    protected function getCreationForm()
    {
        return new CountryCreationForm($this->getRequest());
    }

    /**
     * Return the update form for this object
     */
    protected function getUpdateForm()
    {
        return new CountryModificationForm($this->getRequest());
    }

    /**
     * Hydrate the update form for this object, before passing it to the update template
     *
     * @param \Thelia\Model\Country $object
     */
    protected function hydrateObjectForm($object)
    {
        $data = array(
            'id' => $object->getId(),
            'locale' => $object->getLocale(),
            'title' => $object->getTitle(),
            'isocode' => $object->getIsocode(),
            'isoalpha2' => $object->getIsoalpha2(),
            'isoalpha3' => $object->getIsoalpha3(),
        );

        return new CountryModificationForm($this->getRequest(), 'form', $data);
    }

    /**
     * Creates the creation event with the provided form data
     *
     * @param unknown $formData
     */
    protected function getCreationEvent($formData)
    {
        $event = new CountryCreateEvent();

        return $this->hydrateEvent($event, $formData);
    }

    /**
     * Creates the update event with the provided form data
     *
     * @param unknown $formData
     */
    protected function getUpdateEvent($formData)
    {
        $event = new CountryUpdateEvent($formData['id']);

        return $this->hydrateEvent($event, $formData);
    }

    protected function hydrateEvent($event, $formData)
    {
        $event
            ->setLocale($formData['locale'])
            ->setTitle($formData['title'])
            ->setIsocode($formData['isocode'])
            ->setIsoAlpha2($formData['isoalpha2'])
            ->setIsoAlpha3($formData['isoalpha3'])
            ->setArea($formData['area'])
        ;

        return $event;
    }

    /**
     * Creates the delete event with the provided form data
     */
    protected function getDeleteEvent()
    {
        return new CountryDeleteEvent($this->getRequest()->get('country_id'));
    }

    /**
     * Return true if the event contains the object, e.g. the action has updated the object in the event.
     *
     * @param unknown $event
     */
    protected function eventContainsObject($event)
    {
        return $event->hasCountry();
    }

    /**
     * Get the created object from an event.
     *
     * @param unknown $createEvent
     */
    protected function getObjectFromEvent($event)
    {
        return $event->getCountry();
    }

    /**
     * Load an existing object from the database
     */
    protected function getExistingObject()
    {
        $country = CountryQuery::create()
            ->findPk($this->getRequest()->get('country_id', 0));

        if (null !== $country) {
            $country->setLocale($this->getCurrentEditionLocale());
        }

        return $country;
    }

    /**
     * Returns the object label form the object event (name, title, etc.)
     *
     * @param \Thelia\Model\Country $object
     */
    protected function getObjectLabel($object)
    {
        return $object->getTitle();
    }

    /**
     * Returns the object ID from the object
     *
     * @param \Thelia\Model\Country $object
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
        return $this->render("countries", array("display_country" => 20));
    }

    /**
     * Render the edition template
     */
    protected function renderEditionTemplate()
    {
        return $this->render('country-edit', $this->getEditionArgument());
    }

    protected function getEditionArgument()
    {
        return array(
            'country_id'  => $this->getRequest()->get('country_id', 0)
        );
    }

    /**
     * Redirect to the edition template
     */
    protected function redirectToEditionTemplate()
    {
        $this->redirectToRoute('admin.configuration.countries.update', array(), array(
                "country_id" => $this->getRequest()->get('country_id', 0)
            )
        );
    }

    /**
     * Redirect to the list template
     */
    protected function redirectToListTemplate()
    {
        $this->redirectToRoute('admin.configuration.countries.default');
    }

    public function toggleDefaultAction()
    {
        if (null !== $response = $this->checkAuth($this->resourceCode, array(), AccessManager::UPDATE)) return $response;

        if (null !== $country_id = $this->getRequest()->get('country_id')) {
            $toogleDefaultEvent = new CountryToggleDefaultEvent($country_id);
            try {
                $this->dispatch(TheliaEvents::COUNTRY_TOGGLE_DEFAULT, $toogleDefaultEvent);

                if ($toogleDefaultEvent->hasCountry()) {
                    return $this->nullResponse();
                }
            } catch (\Exception $ex) {
                Tlog::getInstance()->error($ex->getMessage());
            }

        }

        return $this->nullResponse(500);
    }
}
