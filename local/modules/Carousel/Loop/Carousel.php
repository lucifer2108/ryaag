<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Carousel\Loop;

use Carousel\Model\CarouselQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Event\Image\ImageEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Template\Loop\Image;
use Thelia\Log\Tlog;
use Thelia\Type\EnumListType;
use Thelia\Type\EnumType;
use Thelia\Type\TypeCollection;

/**
 * Class CarouselLoop.
 *
 * @author manuel raynaud <mraynaud@openstudio.fr>
 */
class Carousel extends Image
{
    /**
     * {@inheritdoc}
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('width'),
            Argument::createIntTypeArgument('height'),
            Argument::createIntTypeArgument('rotation', 0),
            Argument::createAnyTypeArgument('background_color'),
            Argument::createIntTypeArgument('quality'),
            new Argument(
                'resize_mode',
                new TypeCollection(
                    new EnumType(['crop', 'borders', 'none'])
                ),
                'none'
            ),
            new Argument(
                'order',
                new TypeCollection(
                    new EnumListType(['alpha', 'alpha-reverse', 'manual', 'manual-reverse', 'random'])
                ),
                'manual'
            ),
            Argument::createAnyTypeArgument('effects'),
            Argument::createBooleanTypeArgument('allow_zoom', false),
            Argument::createBooleanTypeArgument('filter_disable_slides', true),
            Argument::createAlphaNumStringTypeArgument('group'),
            Argument::createAlphaNumStringTypeArgument('format')
        );
    }

    /**
     * @return LoopResult
     *
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function parseResults(LoopResult $loopResult)
    {
        /** @var \Carousel\Model\Carousel $carousel */
        foreach ($loopResult->getResultDataCollection() as $carousel) {
            $imgSourcePath = $carousel->getUploadDir().DS.$carousel->getFile();
            if (!file_exists($imgSourcePath)) {
                Tlog::getInstance()->error(sprintf('Carousel source image file %s does not exists.', $imgSourcePath));
                continue;
            }

            $startDate = $carousel->getStartDate();
            $endDate = $carousel->getEndDate();

            if ($carousel->getLimited()) {
                $now = new \DateTime();
                if ($carousel->getDisable()) {
                    if ($now > $startDate && $now < $endDate) {
                        $carousel
                            ->setDisable(0)
                            ->save();
                    }
                } else {
                    if ($now < $startDate || $now > $endDate) {
                        $carousel
                            ->setDisable(1)
                            ->save();
                    }
                }
            }

            if ($this->getFilterDisableSlides() && $carousel->getDisable()) {
                continue;
            }

            $loopResultRow = new LoopResultRow($carousel);

            $event = new ImageEvent();
            $event->setSourceFilepath($imgSourcePath)
                ->setCacheSubdirectory('carousel');

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

            // Prepare tranformations
            $width = $this->getWidth();
            $height = $this->getHeight();
            $rotation = $this->getRotation();
            $background_color = $this->getBackgroundColor();
            $quality = $this->getQuality();
            $effects = $this->getEffects();
            $format = $this->getFormat();

            if (null !== $width) {
                $event->setWidth($width);
            }
            if (null !== $height) {
                $event->setHeight($height);
            }
            $event->setResizeMode($resize_mode);
            if (null !== $rotation) {
                $event->setRotation($rotation);
            }
            if (null !== $background_color) {
                $event->setBackgroundColor($background_color);
            }
            if (null !== $quality) {
                $event->setQuality($quality);
            }
            if (null !== $effects) {
                $event->setEffects($effects);
            }
            if (null !== $format) {
                $event->setFormat($format);
            }

            $event->setAllowZoom($this->getAllowZoom());

            // Dispatch image processing event
            $this->dispatcher->dispatch($event, TheliaEvents::IMAGE_PROCESS);

            if ($startDate) {
                $startDate = $startDate->format('Y-m-d').'T'.$startDate->format('H:i');
            }
            if ($endDate) {
                $endDate = $endDate->format('Y-m-d').'T'.$endDate->format('H:i');
            }

            $loopResultRow
                ->set('ID', $carousel->getId())
                ->set('LOCALE', $this->locale)
                ->set('IMAGE_URL', $event->getFileUrl())
                ->set('ORIGINAL_IMAGE_URL', $event->getOriginalFileUrl())
                ->set('IMAGE_PATH', $event->getCacheFilepath())
                ->set('ORIGINAL_IMAGE_PATH', $event->getSourceFilepath())
                ->set('TITLE', $carousel->getVirtualColumn('i18n_TITLE'))
                ->set('CHAPO', $carousel->getVirtualColumn('i18n_CHAPO'))
                ->set('DESCRIPTION', $carousel->getVirtualColumn('i18n_DESCRIPTION'))
                ->set('POSTSCRIPTUM', $carousel->getVirtualColumn('i18n_POSTSCRIPTUM'))
                ->set('ALT', $carousel->getVirtualColumn('i18n_ALT'))
                ->set('URL', $carousel->getUrl())
                ->set('POSITION', $carousel->getPosition())
                ->set('DISABLE', $carousel->getDisable())
                ->set('GROUP', $carousel->getGroup())
                ->set('LIMITED', $carousel->getLimited())
                ->set('START_DATE', $startDate)
                ->set('END_DATE', $endDate)
            ;

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }

    /**
     * this method returns a Propel ModelCriteria.
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function buildModelCriteria()
    {
        $search = CarouselQuery::create();
        $group = $this->getGroup();

        $this->configureI18nProcessing($search, ['ALT', 'TITLE', 'CHAPO', 'DESCRIPTION', 'POSTSCRIPTUM']);

        $orders = $this->getOrder();

        // Results ordering
        foreach ($orders as $order) {
            switch ($order) {
                case 'alpha':
                    $search->addAscendingOrderByColumn('i18n_TITLE');
                    break;
                case 'alpha-reverse':
                    $search->addDescendingOrderByColumn('i18n_TITLE');
                    break;
                case 'manual-reverse':
                    $search->orderByPosition(Criteria::DESC);
                    break;
                case 'manual':
                    $search->orderByPosition(Criteria::ASC);
                    break;
                case 'random':
                    $search->clearOrderByColumns();
                    $search->addAscendingOrderByColumn('RAND()');
                    break 2;
                    break;
            }
        }

        if ($group) {
            $search->filterByGroup($group);
        }

        return $search;
    }
}
