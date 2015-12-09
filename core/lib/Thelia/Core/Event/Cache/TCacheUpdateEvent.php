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


namespace Thelia\Core\Event\Cache;
use Thelia\Core\Event\ActionEvent;


/**
 * Class TCacheUpdateEvent
 * @package Thelia\Core\Event\Cache
 * @author Julien Chanséaume <jchanseaume@openstudio.fr>
 */
class TCacheUpdateEvent extends ActionEvent
{

    public function all()
    {
        return $this->parameters;
    }
} 