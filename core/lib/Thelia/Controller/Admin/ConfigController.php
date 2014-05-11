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
use Thelia\Core\Event\Config\ConfigDeleteEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\Config\ConfigUpdateEvent;
use Thelia\Core\Event\Config\ConfigCreateEvent;
use Thelia\Core\Security\AccessManager;
use Thelia\Model\ConfigQuery;
use Thelia\Form\ConfigModificationForm;
use Thelia\Form\ConfigCreationForm;

/**
 * Manages variables
 *
 * @author Franck Allimant <franck@cqfdev.fr>
 */
class ConfigController extends AbstractCrudController
{
    public function __construct()
    {
        parent::__construct(
            'variable',
            'name',
            'order',

            AdminResources::CONFIG,

            TheliaEvents::CONFIG_CREATE,
            TheliaEvents::CONFIG_UPDATE,
            TheliaEvents::CONFIG_DELETE,
            null, // No visibility toggle
            null // no position change
        );
    }

    protected function getCreationForm()
    {
        return new ConfigCreationForm($this->getRequest());
    }

    protected function getUpdateForm()
    {
        return new ConfigModificationForm($this->getRequest());
    }

    protected function getCreationEvent($data)
    {
        $createEvent = new ConfigCreateEvent();

        $createEvent
            ->setEventName($data['name'])
            ->setValue($data['value'])
            ->setLocale($data["locale"])
            ->setTitle($data['title'])
            ->setHidden($data['hidden'])
            ->setSecured($data['secured'])
            ;

        return $createEvent;
    }

    protected function getUpdateEvent($data)
    {
        $changeEvent = new ConfigUpdateEvent($data['id']);

        // Create and dispatch the change event
        $changeEvent
            ->setEventName($data['name'])
            ->setValue($data['value'])
            ->setHidden($data['hidden'])
            ->setSecured($data['secured'])
            ->setLocale($data["locale"])
            ->setTitle($data['title'])
            ->setChapo($data['chapo'])
            ->setDescription($data['description'])
            ->setPostscriptum($data['postscriptum'])
        ;

        return $changeEvent;
    }

    protected function getDeleteEvent()
    {
        return new ConfigDeleteEvent($this->getRequest()->get('variable_id'));
    }

    protected function eventContainsObject($event)
    {
        return $event->hasConfig();
    }

    protected function hydrateObjectForm($object)
    {
        // Prepare the data that will hydrate the form
        $data = array(
            'id'           => $object->getId(),
            'name'         => $object->getName(),
            'value'        => $object->getValue(),
            'hidden'       => $object->getHidden(),
            'secured'      => $object->getSecured(),
            'locale'       => $object->getLocale(),
            'title'        => $object->getTitle(),
            'chapo'        => $object->getChapo(),
            'description'  => $object->getDescription(),
            'postscriptum' => $object->getPostscriptum()
        );

        // Setup the object form
        return new ConfigModificationForm($this->getRequest(), "form", $data);
    }

    protected function getObjectFromEvent($event)
    {
        return $event->hasConfig() ? $event->getConfig() : null;
    }

    protected function getExistingObject()
    {
        $config = ConfigQuery::create()
        ->findOneById($this->getRequest()->get('variable_id'));

        if (null !== $config) {
            $config->setLocale($this->getCurrentEditionLocale());
        }

        return $config;
    }

    protected function getObjectLabel($object)
    {
        return $object->getName();
    }

    protected function getObjectId($object)
    {
        return $object->getId();
    }

    protected function renderListTemplate($currentOrder)
    {
        return $this->render('variables', array('order' => $currentOrder));
    }

    protected function renderEditionTemplate()
    {
        return $this->render('variable-edit', array('variable_id' => $this->getRequest()->get('variable_id')));
    }

    protected function redirectToEditionTemplate()
    {
        $this->redirectToRoute(
                "admin.configuration.variables.update",
                array('variable_id' => $this->getRequest()->get('variable_id'))
        );
    }

    protected function redirectToListTemplate()
    {
        $this->redirectToRoute('admin.configuration.variables.default');
    }

    /**
     * Change values modified directly from the variable list
     *
     * @return Thelia\Core\HttpFoundation\Response the response
     */
    public function changeValuesAction()
    {
        // Check current user authorization
        if (null !== $response = $this->checkAuth($this->resourceCode, array(), AccessManager::UPDATE)) return $response;

        $variables = $this->getRequest()->get('variable', array());

        // Process all changed variables
        foreach ($variables as $id => $value) {
            $event = new ConfigUpdateEvent($id);
            $event->setValue($value);

            $this->dispatch(TheliaEvents::CONFIG_SETVALUE, $event);
        }

        $this->redirectToRoute('admin.configuration.variables.default');
    }
}
