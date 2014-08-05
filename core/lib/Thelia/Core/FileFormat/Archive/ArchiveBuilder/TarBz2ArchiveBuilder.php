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

namespace Thelia\Core\FileFormat\Archive\ArchiveBuilder;

/**
 * Class TarBz2ArchiveBuilder
 * @package Thelia\Core\FileFormat\Archive\ArchiveBuilder
 * @author Benjamin Perche <bperche@openstudio.fr>
 */
class TarBz2ArchiveBuilder extends TarArchiveBuilder
{
    public function getName()
    {
        return "tar.bz2";
    }

    public function getMimeType()
    {
        return "application/x-bzip2";
    }

    public function getExtension()
    {
        return "tbz2";
    }

    public function getCacheExtension()
    {
        return "tar.bz2";
    }

    protected function compressionEntryPoint()
    {
        if ($this->compression != \Phar::BZ2) {
            $this->tar = $this->tar->compress(\Phar::BZ2, $this->getCacheExtension());
        }

        $this->compression = \Phar::BZ2;

        return $this;
    }
}
