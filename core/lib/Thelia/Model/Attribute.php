<?php

namespace Thelia\Model;

use Thelia\Model\Base\Attribute as BaseAttribute;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Event\Attribute\AttributeEvent;

class Attribute extends BaseAttribute
{
    use \Thelia\Model\Tools\ModelEventDispatcherTrait;
    use \Thelia\Model\Tools\PositionManagementTrait;

    /**
     * {@inheritDoc}
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        $this->dispatchEvent(TheliaEvents::BEFORE_CREATEATTRIBUTE, new AttributeEvent($this));

        // Set the current position for the new object
        $this->setPosition($this->getNextPosition());

        parent::preInsert($con);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function postInsert(ConnectionInterface $con = null)
    {
        parent::postInsert($con);

        $this->dispatchEvent(TheliaEvents::AFTER_CREATEATTRIBUTE, new AttributeEvent($this));
    }

    /**
     * {@inheritDoc}
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        parent::preUpdate($con);

        $this->dispatchEvent(TheliaEvents::BEFORE_UPDATEATTRIBUTE, new AttributeEvent($this));

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function postUpdate(ConnectionInterface $con = null)
    {
        parent::postUpdate($con);

        $this->dispatchEvent(TheliaEvents::AFTER_UPDATEATTRIBUTE, new AttributeEvent($this));
    }

    /**
     * {@inheritDoc}
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        parent::preDelete($con);

        $this->dispatchEvent(TheliaEvents::BEFORE_DELETEATTRIBUTE, new AttributeEvent($this));

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function postDelete(ConnectionInterface $con = null)
    {
        parent::postDelete($con);

        $this->dispatchEvent(TheliaEvents::AFTER_DELETEATTRIBUTE, new AttributeEvent($this));
    }
}
