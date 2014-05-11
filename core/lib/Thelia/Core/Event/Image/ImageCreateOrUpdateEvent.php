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

namespace Thelia\Core\Event\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Thelia\Core\Event\ActionEvent;

/**
 * Created by JetBrains PhpStorm.
 * Date: 9/18/13
 * Time: 3:56 PM
 *
 * Occurring when an Image is saved
 *
 * @package Image
 * @author  Guillaume MOREL <gmorel@openstudio.fr>
 *
 */
class ImageCreateOrUpdateEvent extends ActionEvent
{

    /** @var \Thelia\Model\CategoryImage|\Thelia\Model\ProductImage|\Thelia\Model\ContentImage|\Thelia\Model\FolderImage model to save */
    protected $modelImage = array();

    /** @var \Thelia\Model\CategoryImage|\Thelia\Model\ProductImage|\Thelia\Model\ContentImage|\Thelia\Model\FolderImage model to save */
    protected $oldModelImage = array();

    /** @var UploadedFile Image file to save */
    protected $uploadedFile = null;

    /** @var int Image parent id */
    protected $parentId = null;

    /** @var string Image type */
    protected $imageType = null;

    /** @var string Parent name */
    protected $parentName = null;

    protected $locale;

    /**
     * Constructor
     *
     * @param string $imageType Image type
     *                          ex : FileManager::TYPE_CATEGORY
     * @param int    $parentId  Image parent id
     */
    public function __construct($imageType, $parentId)
    {
        $this->imageType = $imageType;
        $this->parentId  = $parentId;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set Image to save
     *
     * @param $image \Thelia\Model\CategoryImage|\Thelia\Model\ProductImage|\Thelia\Model\ContentImage|\Thelia\Model\FolderImage
     *
     * @return $this
     */
    public function setModelImage($image)
    {
        $this->modelImage = $image;

        return $this;
    }

    /**
     * Get Image being saved
     *
     * @return \Thelia\Model\CategoryImage|\Thelia\Model\ProductImage|\Thelia\Model\ContentImage|\Thelia\Model\FolderImage
     */
    public function getModelImage()
    {
        return $this->modelImage;
    }

    /**
     * Set picture type
     *
     * @param string $imageType Image type
     *
     * @return $this
     */
    public function setImageType($imageType)
    {
        $this->imageType = $imageType;

        return $this;
    }

    /**
     * Get picture type
     *
     * @return string
     */
    public function getImageType()
    {
        return $this->imageType;
    }

    /**
     * Set Image parent id
     *
     * @param int $parentId Image parent id
     *
     * @return $this
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get Image parent id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set uploaded file
     *
     * @param UploadedFile $uploadedFile File being uploaded
     *
     * @return $this
     */
    public function setUploadedFile($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;

        return $this;
    }

    /**
     * Get uploaded file
     *
     * @return UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * Set parent name
     *
     * @param string $parentName Parent name
     *
     * @return $this
     */
    public function setParentName($parentName)
    {
        $this->parentName = $parentName;

        return $this;
    }

    /**
     * Get parent name
     *
     * @return string
     */
    public function getParentName()
    {
        return $this->parentName;
    }

    /**
     * Set old model value
     *
     * @param \Thelia\Model\CategoryImage|\Thelia\Model\ContentImage|\Thelia\Model\FolderImage|\Thelia\Model\ProductImage $oldModelImage
     */
    public function setOldModelImage($oldModelImage)
    {
        $this->oldModelImage = $oldModelImage;
    }

    /**
     * Get old model value
     *
     * @return \Thelia\Model\CategoryImage|\Thelia\Model\ContentImage|\Thelia\Model\FolderImage|\Thelia\Model\ProductImage
     */
    public function getOldModelImage()
    {
        return $this->oldModelImage;
    }

}
