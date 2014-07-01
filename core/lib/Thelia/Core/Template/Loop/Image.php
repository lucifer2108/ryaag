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

namespace Thelia\Core\Template\Loop;

use Thelia\Core\Template\Element\BaseI18nLoop;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Event\Image\ImageEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Type\BooleanOrBothType;
use Thelia\Type\TypeCollection;
use Thelia\Type\EnumListType;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Model\ConfigQuery;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Type\EnumType;
use Thelia\Log\Tlog;

/**
 * The image loop
 *
 * @author Franck Allimant <franck@cqfdev.fr>
 */
class Image extends BaseI18nLoop implements PropelSearchLoopInterface
{
    protected $objectType;
    protected $objectId;

    protected $timestampable = true;

    protected $isCacheable = true;

    /**
     * @var array Possible standard image sources
     */
    protected $possible_sources = array('category', 'product', 'folder', 'content', 'module', 'brand');

    /**
     * @return \Thelia\Core\Template\Loop\Argument\ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        $collection = new ArgumentCollection(
            Argument::createIntListTypeArgument('id'),
            Argument::createIntListTypeArgument('exclude'),
            Argument::createBooleanOrBothTypeArgument('visible', 1),
            new Argument(
                'order',
                new TypeCollection(
                    new EnumListType(array('alpha', 'alpha-reverse', 'manual', 'manual-reverse', 'random'))
                ),
                'manual'
            ),
            Argument::createIntTypeArgument('width'),
            Argument::createIntTypeArgument('height'),
            Argument::createIntTypeArgument('rotation', 0),
            Argument::createAnyTypeArgument('background_color'),
            Argument::createIntTypeArgument('quality'),
            new Argument(
                'resize_mode',
                new TypeCollection(
                    new EnumType(array('crop', 'borders', 'none'))
                ),
                'none'
            ),
            Argument::createAnyTypeArgument('effects'),
            Argument::createIntTypeArgument('category'),
            Argument::createIntTypeArgument('product'),
            Argument::createIntTypeArgument('folder'),
            Argument::createIntTypeArgument('content'),
            Argument::createAnyTypeArgument('source'),
            Argument::createIntTypeArgument('source_id'),
            Argument::createBooleanTypeArgument('force_return', true)
        );

        // Add possible image sources
        foreach ($this->possible_sources as $source) {
            $collection->addArgument(Argument::createIntTypeArgument($source));
        }

        return $collection;
    }

    /**
     * Dynamically create the search query, and set the proper filter and order
     *
     * @param  string        $source    a valid source identifier (@see $possible_sources)
     * @param  int           $object_id the source object ID
     * @return ModelCriteria the propel Query object
     */
    protected function createSearchQuery($source, $object_id)
    {
        $object = ucfirst($source);

        $queryClass   = sprintf("\Thelia\Model\%sImageQuery", $object);
        $filterMethod = sprintf("filterBy%sId", $object);

        // xxxImageQuery::create()
        $method = new \ReflectionMethod($queryClass, 'create');
        $search = $method->invoke(null); // Static !

        // $query->filterByXXX(id)
        if (! is_null($object_id)) {
            $method = new \ReflectionMethod($queryClass, $filterMethod);
            $method->invoke($search, $object_id);
        }

        $orders  = $this->getOrder();

        // Results ordering
        foreach ($orders as $order) {
            switch ($order) {
                case "alpha":
                    $search->addAscendingOrderByColumn('i18n_TITLE');
                    break;
                case "alpha-reverse":
                    $search->addDescendingOrderByColumn('i18n_TITLE');
                    break;
                case "manual-reverse":
                    $search->orderByPosition(Criteria::DESC);
                    break;
                case "manual":
                    $search->orderByPosition(Criteria::ASC);
                    break;
                case "random":
                    $search->clearOrderByColumns();
                    $search->addAscendingOrderByColumn('RAND()');
                    break(2);
                    break;
            }
        }

        return $search;
    }

    /**
     * Dynamically create the search query, and set the proper filter and order
     *
     * @param  string        $object_type (returned) the a valid source identifier (@see $possible_sources)
     * @param  string        $object_id   (returned) the ID of the source object
     * @return ModelCriteria the propel Query object
     */
    protected function getSearchQuery(&$object_type, &$object_id)
    {
        $search = null;

        // Check form source="product" source_id="123" style arguments
        $source = $this->getSource();

        if (! is_null($source)) {
            $source_id = $this->getSourceId();
            $id = $this->getId();

            if (is_null($source_id) && is_null($id)) {
                throw new \InvalidArgumentException("If 'source' argument is specified, 'id' or 'source_id' argument should be specified");
            }

            $search = $this->createSearchQuery($source, $source_id);

            $object_type = $source;
            $object_id   = $source_id;
        } else {
            // Check for product="id" folder="id", etc. style arguments
            foreach ($this->possible_sources as $source) {
                $argValue = $this->getArgValue($source);

                if (! empty($argValue)) {
                    $argValue = intval($argValue);

                    $search = $this->createSearchQuery($source, $argValue);

                    $object_type = $source;
                    $object_id   = $argValue;

                    break;
                }
            }
        }

        if ($search == null) {
            throw new \InvalidArgumentException(sprintf("Unable to find image source. Valid sources are %s", implode(',', $this->possible_sources)));
        }

        return $search;
    }

    public function buildModelCriteria()
    {
        // Select the proper query to use, and get the object type
        $this->objectType = $this->objectId = null;

        $search = $this->getSearchQuery($this->objectType, $this->objectId);

        /* manage translations */
        $this->configureI18nProcessing($search);

        $id = $this->getId();

        if (! is_null($id)) {
            $search->filterById($id, Criteria::IN);
        }

        $exclude = $this->getExclude();
        if (!is_null($exclude)) {
            $search->filterById($exclude, Criteria::NOT_IN);
        }

        $visible = $this->getVisible();
        if ($visible !== BooleanOrBothType::ANY) {
            $search->filterByVisible($visible ? 1 : 0);
        }

        return $search;
    }

    public function parseResults(LoopResult $loopResult)
    {
        // Create image processing event
        $event = new ImageEvent($this->request);

        // Prepare tranformations
        $width = $this->getWidth();
        $height = $this->getHeight();
        $rotation = $this->getRotation();
        $background_color = $this->getBackgroundColor();
        $quality = $this->getQuality();
        $effects = $this->getEffects();

        if (! is_null($effects)) {
            $effects = explode(',', $effects);
        }

        switch ($this->getResizeMode()) {
            case 'crop':
                $resize_mode = \Thelia\Action\Image::EXACT_RATIO_WITH_CROP;
                break;

            case 'borders':
                $resize_mode = \Thelia\Action\Image::EXACT_RATIO_WITH_BORDERS;
                break;

            case 'none':
            default:
                $resize_mode = \Thelia\Action\Image::KEEP_IMAGE_RATIO;

        }

        foreach ($loopResult->getResultDataCollection() as $result) {
            // Setup required transformations
            if (! is_null($width)) {
                $event->setWidth($width);
            }
            if (! is_null($height)) {
                $event->setHeight($height);
            }
            $event->setResizeMode($resize_mode);
            if (! is_null($rotation)) {
                $event->setRotation($rotation);
            }
            if (! is_null($background_color)) {
                $event->setBackgroundColor($background_color);
            }
            if (! is_null($quality)) {
                $event->setQuality($quality);
            }
            if (! is_null($effects)) {
                $event->setEffects($effects);
            }

            // Put source image file path
            $source_filepath = sprintf(
                "%s%s/%s/%s",
                THELIA_ROOT,
                ConfigQuery::read('images_library_path', 'local/media/images'),
                $this->objectType,
                $result->getFile()
            );

            $event->setSourceFilepath($source_filepath);
            $event->setCacheSubdirectory($this->objectType);

            try {
                // Dispatch image processing event
                $this->dispatcher->dispatch(TheliaEvents::IMAGE_PROCESS, $event);

                $loopResultRow = new LoopResultRow($result);

                $loopResultRow
                    ->set("ID", $result->getId())
                    ->set("LOCALE", $this->locale)
                    ->set("IMAGE_URL", $event->getFileUrl())
                    ->set("ORIGINAL_IMAGE_URL", $event->getOriginalFileUrl())
                    ->set("IMAGE_PATH", $event->getCacheFilepath())
                    ->set("ORIGINAL_IMAGE_PATH", $source_filepath)
                    ->set("TITLE", $result->getVirtualColumn('i18n_TITLE'))
                    ->set("CHAPO", $result->getVirtualColumn('i18n_CHAPO'))
                    ->set("DESCRIPTION", $result->getVirtualColumn('i18n_DESCRIPTION'))
                    ->set("POSTSCRIPTUM", $result->getVirtualColumn('i18n_POSTSCRIPTUM'))
                    ->set("VISIBLE", $result->getVisible())
                    ->set("POSITION", $result->getPosition())
                    ->set("OBJECT_TYPE", $this->objectType)
                    ->set("OBJECT_ID", $this->objectId)
                ;
                $this->addOutputFields($loopResultRow, $result);

                $loopResult->addRow($loopResultRow);
            } catch (\Exception $ex) {
                // Ignore the result and log an error
                Tlog::getInstance()->addError(sprintf("Failed to process image in image loop: %s", $ex->getMessage()));
            }
        }

        return $loopResult;
    }


    protected function isCacheable(){

        $this->object_type = null;
        $this->object_id  = null;

        $source = $this->getSource();
        if (! is_null($source)) {

            $source_id = $this->getSourceId();
            $id = $this->getId();

            if (is_null($source_id) && is_null($id)) {
                throw new \InvalidArgumentException("If 'source' argument is specified, 'id' or 'source_id' argument should be specified");
            }

            $this->object_type = $source;
            $this->object_id   = $source_id;

            return true;
        } else {
            foreach ($this->possible_sources as $source) {

                $argValue = intval($this->getArgValue($source));

                if ($argValue > 0) {

                    $this->object_type = $source;
                    $this->object_id   = $argValue;

                    return true;
                }
            }
        }

        return false;
    }

    public function getCacheRef(){
        return sprintf("%s::%s", $this->object_type, $this->object_id);
    }

}
