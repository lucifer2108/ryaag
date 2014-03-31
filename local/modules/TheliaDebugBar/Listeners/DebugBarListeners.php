<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace TheliaDebugBar\Listeners;

use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DebugBar;
use TheliaDebugBar\DataCollector\PropelCollector;
use DebugBar\DataCollector\TimeDataCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Action\BaseAction;
use Thelia\Core\Event\TheliaEvents;


/**
 * Class DebugBarListeners
 * @package TheliaDebugBar\Listeners
 * @author Manuel Raynaud <mraynaud@openstudio.fr>
 */
class DebugBarListeners extends BaseAction implements EventSubscriberInterface {

    protected $debugBar;
    protected $debugMode;

    public function __construct(DebugBar $debugbar, $debugMode)
    {
        $this->debugBar = $debugbar;
        $this->debugMode = $debugMode;
    }

    public function initDebugBar()
    {
        $alternativelogger = null;
        if($this->debugMode) {
            $alternativelogger = \Thelia\Log\Tlog::getInstance();
        }

        $this->debugBar->addCollector(new PhpInfoCollector());
        //$this->debugBar->addCollector(new MessagesCollector());
        //$this->debugBar->addCollector(new RequestDataCollector());
        $this->debugBar->addCollector(new TimeDataCollector());
        $this->debugBar->addCollector(new MemoryCollector());
        $this->debugBar->addCollector(new PropelCollector($alternativelogger));
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
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
            TheliaEvents::BOOT => array("initDebugBar", 128)
        );
    }
}