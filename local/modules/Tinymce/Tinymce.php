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

namespace Tinymce;

use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Thelia\Module\BaseModule;

class Tinymce extends BaseModule
{
    /**
     * YOU HAVE TO IMPLEMENT HERE ABSTRACT METHODD FROM BaseModule Class
     * Like install and destroy
     */
    public function postActivation(ConnectionInterface $con = null)
    {
        $fs = new Filesystem();

        $fs->mirror(__DIR__ . DS .'Resources'.DS.'js'.DS.'tinymce', THELIA_WEB_DIR . 'tinymce');
        $fs->symlink(__DIR__ . DS .'Resources'.DS.'media', THELIA_WEB_DIR . 'media');
    }
}
