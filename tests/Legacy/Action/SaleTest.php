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

namespace Thelia\Tests\Action;

use Thelia\Action\Sale;
use Thelia\Core\Event\Sale\SaleClearStatusEvent;
use Thelia\Core\Event\Sale\SaleCreateEvent;
use Thelia\Core\Event\Sale\SaleDeleteEvent;
use Thelia\Core\Event\Sale\SaleToggleActivityEvent;
use Thelia\Core\Event\Sale\SaleUpdateEvent;
use Thelia\Model\AttributeAvQuery;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Model\Sale as SaleModel;
use Thelia\Model\SaleQuery;
use Thelia\Tests\TestCaseWithURLToolSetup;

/**
 * Class SaleTest.
 *
 * @author Manuel Raynaud <manu@raynaud.io>
 */
class SaleTest extends TestCaseWithURLToolSetup
{
    public function getUpdateEvent(&$sale)
    {
        if (!$sale instanceof SaleModel) {
            $sale = $this->getRandomSale();
        }

        $event = new SaleUpdateEvent($sale->getId());
        $event
            ->setActive(1)
            ->setLocale($sale->getLocale())
            ->setTitle($sale->getTitle())
            ->setChapo($sale->getChapo())
            ->setDescription($sale->getDescription())
            ->setPostscriptum($sale->getPostscriptum())
        ;

        return $event;
    }

    /**
     * @param SaleUpdateEvent $event
     *
     * @throws \Exception
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return SaleModel
     */
    public function processUpdateAction($event)
    {
        $saleAction = new Sale();
        $saleAction->update($event, null, $this->getMockEventDispatcher());

        return $event->getSale();
    }

    public function testCreateSale(): void
    {
        $event = new SaleCreateEvent();
        $event
            ->setLocale('en_US')
            ->setTitle('test create sale')
            ->setSaleLabel('test create sale label')
        ;

        $saleAction = new Sale($this->getMockEventDispatcher());
        $saleAction->create($event);

        $createdSale = $event->getSale();

        $this->assertInstanceOf('Thelia\Model\Sale', $createdSale);
        $this->assertEquals('test create sale', $createdSale->getTitle());
        $this->assertEquals('test create sale label', $createdSale->getSaleLabel());
    }

    public function testUpdateSale(): void
    {
        $sale = $this->getRandomSale();

        $date = new \DateTime();

        $product = ProductQuery::create()->findOne();

        $event = new SaleUpdateEvent($sale->getId());
        $event
            ->setStartDate($date->setTimestamp(strtotime('today - 1 month')))
            ->setEndDate($date->setTimestamp(strtotime('today + 1 month')))
            ->setActive(1)
            ->setDisplayInitialPrice(1)
            ->setPriceOffsetType(SaleModel::OFFSET_TYPE_AMOUNT)
            ->setPriceOffsets([CurrencyQuery::create()->findOne()->getId() => 10])
            ->setProducts([$product->getId()])
            ->setProductAttributes([])
            ->setLocale('en_US')
            ->setTitle('test update sale title')
            ->setChapo('test update sale short description')
            ->setDescription('test update sale description')
            ->setPostscriptum('test update sale postscriptum')
            ->setSaleLabel('test create sale label')
        ;

        $saleAction = new Sale();
        $saleAction->update($event, null, $this->getMockEventDispatcher());

        $updatedSale = $event->getSale();

        $this->assertInstanceOf('Thelia\Model\Sale', $updatedSale);
        $this->assertEquals(1, $updatedSale->getActive());
        $this->assertEquals('test update sale title', $updatedSale->getTitle());
        $this->assertEquals('test update sale short description', $updatedSale->getChapo());
        $this->assertEquals('test update sale description', $updatedSale->getDescription());
        $this->assertEquals('test update sale postscriptum', $updatedSale->getPostscriptum());
        $this->assertEquals('test create sale label', $updatedSale->getSaleLabel());
    }

    public function testUpdatePseSale(): void
    {
        $sale = $this->getRandomSale();

        $date = new \DateTime();

        $product = ProductQuery::create()->findOne();
        $attrAv = AttributeAvQuery::create()->findOne();

        $event = new SaleUpdateEvent($sale->getId());
        $event
            ->setStartDate($date->setTimestamp(strtotime('today - 1 month')))
            ->setEndDate($date->setTimestamp(strtotime('today + 1 month')))
            ->setActive(1)
            ->setDisplayInitialPrice(1)
            ->setPriceOffsetType(SaleModel::OFFSET_TYPE_AMOUNT)
            ->setPriceOffsets([CurrencyQuery::create()->findOne()->getId() => 10])
            ->setProducts([$product->getId()])
            ->setProductAttributes([$product->getId() => [$attrAv->getId()]])
            ->setLocale('en_US')
            ->setTitle('test update sale title')
            ->setChapo('test update sale short description')
            ->setDescription('test update sale description')
            ->setPostscriptum('test update sale postscriptum')
            ->setSaleLabel('test create sale label')
        ;

        $saleAction = new Sale($this->getMockEventDispatcher());
        $saleAction->update($event, null, $this->getMockEventDispatcher());

        $updatedSale = $event->getSale();

        $this->assertInstanceOf('Thelia\Model\Sale', $updatedSale);
        $this->assertEquals(1, $updatedSale->getActive());
        $this->assertEquals('test update sale title', $updatedSale->getTitle());
        $this->assertEquals('test update sale short description', $updatedSale->getChapo());
        $this->assertEquals('test update sale description', $updatedSale->getDescription());
        $this->assertEquals('test update sale postscriptum', $updatedSale->getPostscriptum());
        $this->assertEquals('test create sale label', $updatedSale->getSaleLabel());
    }

    public function testDeleteSale(): void
    {
        $sale = $this->getRandomSale();

        $event = new SaleDeleteEvent($sale->getId());

        $saleAction = new Sale($this->getMockEventDispatcher());
        $saleAction->delete($event, null, $this->getMockEventDispatcher());

        $deletedSale = $event->getSale();

        $this->assertInstanceOf('Thelia\Model\Sale', $deletedSale);
        $this->assertTrue($deletedSale->isDeleted());
    }

    public function testSaleToggleVisibility(): void
    {
        $sale = $this->getRandomSale();

        $visibility = $sale->getActive();

        $event = new SaleToggleActivityEvent($sale);

        $saleAction = new Sale($this->getMockEventDispatcher());
        $saleAction->toggleActivity($event, null, $this->getMockEventDispatcher());

        $updatedSale = $event->getSale();

        $this->assertInstanceOf('Thelia\Model\Sale', $updatedSale);
        $this->assertEquals(!$visibility, $updatedSale->getActive());
    }

    public function testClearAllSales(): void
    {
        $anExceptionWasThrown = false;

        try {
            // Store current promo statuses
            $promoList = ProductSaleElementsQuery::create()->filterByPromo(true)->select('Id')->find()->toArray();

            $event = new SaleClearStatusEvent();

            $saleAction = new Sale();
            $saleAction->clearStatus($event);

            // Restore promo status
            ProductSaleElementsQuery::create()->filterById($promoList)->update(['Promo' => true]);
        } catch (\Exception $e) {
            $anExceptionWasThrown = true;
        }

        $this->assertFalse($anExceptionWasThrown);
    }

    /**
     * @return \Thelia\Model\Sale
     */
    protected function getRandomSale()
    {
        $sale = SaleQuery::create()
            ->addAscendingOrderByColumn('RAND()')
            ->findOne();

        if (null === $sale) {
            $this->fail('use fixtures before launching test, there is no sale in database');
        }

        return $sale;
    }
}
