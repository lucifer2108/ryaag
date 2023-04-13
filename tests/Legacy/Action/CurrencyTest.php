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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Action\Currency;
use Thelia\Core\Event\Currency\CurrencyCreateEvent;
use Thelia\Core\Event\Currency\CurrencyDeleteEvent;
use Thelia\Core\Event\Currency\CurrencyUpdateEvent;
use Thelia\CurrencyConverter\CurrencyConverter;
use Thelia\CurrencyConverter\Provider\ECBProvider;
use Thelia\Model\Currency as CurrencyModel;
use Thelia\Model\CurrencyQuery;
use Thelia\Tests\ContainerAwareTestCase;

/**
 * Class CurrencyTest.
 *
 * @author Manuel Raynaud <manu@raynaud.io>
 */
class CurrencyTest extends ContainerAwareTestCase
{
    /**
     * @return EventDispatcherInterface
     */
    protected function getMockEventDispatcher()
    {
        return $this->createMock("Symfony\Component\EventDispatcher\EventDispatcherInterface");
    }

    public function testCreate()
    {
        $event = new CurrencyCreateEvent();

        $event
            ->setCurrencyName('test')
            ->setCode('AZE')
            ->setRate('1.35')
            ->setLocale('en_US')
            ->setSymbol('ù')
            ->setFormat('%n %c')
        ;

        $action = new Currency($this->getCurrencyConverter());
        $action->create($event, null, $this->getMockEventDispatcher());

        $createdCurrency = $event->getCurrency();

        $this->assertInstanceOf('Thelia\Model\Currency', $createdCurrency);
        $this->assertFalse($createdCurrency->isNew());

        $this->assertEquals('test', $createdCurrency->getName());
        $this->assertEquals('AZE', $createdCurrency->getCode());
        $this->assertEquals('1.35', $createdCurrency->getRate());
        $this->assertEquals('en_US', $createdCurrency->getLocale());
        $this->assertEquals('ù', $createdCurrency->getSymbol());
        $this->assertEquals('%n %c', $createdCurrency->getFormat());

        return $createdCurrency;
    }

    /**
     * @depends testCreate
     *
     * @return CurrencyModel
     */
    public function testUpdate(CurrencyModel $currency)
    {
        $event = new CurrencyUpdateEvent($currency->getId());

        $event
            ->setCurrencyName('test update')
            ->setCode('AZER')
            ->setRate('2.35')
            ->setLocale('en_US')
            ->setSymbol('ù')
            ->setFormat('%n %c')
        ;

        $action = new Currency($this->getCurrencyConverter());
        $action->update($event, null, $this->getMockEventDispatcher());

        $updatedCurrency = $event->getCurrency();

        $this->assertInstanceOf('Thelia\Model\Currency', $updatedCurrency);
        $this->assertEquals('test update', $updatedCurrency->getName());
        $this->assertEquals('AZER', $updatedCurrency->getCode());
        $this->assertEquals('2.35', $updatedCurrency->getRate());
        $this->assertEquals('en_US', $updatedCurrency->getLocale());
        $this->assertEquals('ù', $updatedCurrency->getSymbol());
        $this->assertEquals('%n %c', $updatedCurrency->getFormat());

        return $updatedCurrency;
    }

    /**
     * @depends testUpdate
     *
     * @return CurrencyModel
     */
    public function testSetDefault(CurrencyModel $currency)
    {
        $event = new CurrencyUpdateEvent($currency->getId());
        $event
            ->setIsDefault(1);

        $action = new Currency($this->getCurrencyConverter());
        $action->setDefault($event, null, $this->getMockEventDispatcher());

        $updatedCurrency = $event->getCurrency();

        $this->assertInstanceOf('Thelia\Model\Currency', $updatedCurrency);

        $this->assertEquals(1, $updatedCurrency->getByDefault());
        $this->assertEquals(1, CurrencyQuery::create()->filterByByDefault(true)->count());

        return $updatedCurrency;
    }

    /**
     * @depends testSetDefault
     */
    public function testDelete(CurrencyModel $currency): void
    {
        $currency->setByDefault(0)
            ->save();

        $event = new CurrencyDeleteEvent($currency->getId());

        $action = new Currency($this->getCurrencyConverter());
        $action->delete($event, null, $this->getMockEventDispatcher());

        $deletedCurrency = $event->getCurrency();

        $this->assertInstanceOf('Thelia\Model\Currency', $deletedCurrency);

        $this->assertTrue($deletedCurrency->isDeleted());
    }

    public function testDeleteDefault(): void
    {
        CurrencyQuery::create()
            ->addAscendingOrderByColumn('RAND()')
            ->limit(1)
            ->update(['ByDefault' => true]);

        $currency = CurrencyQuery::create()->findOneByByDefault(1);

        $event = new CurrencyDeleteEvent($currency->getId());

        $action = new Currency($this->getCurrencyConverter());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('It is not allowed to delete the default currency');
        $action->delete($event, null, $this->getMockEventDispatcher());
    }

    public static function tearDownAfterClass(): void
    {
        CurrencyQuery::create()
            ->addAscendingOrderByColumn('RAND()')
            ->limit(1)
            ->update(['ByDefault' => true]);
    }

    /**
     * @param containerBuilder $container
     *                                    Use this method to build the container with the services that you need
     */
    protected function buildContainer(ContainerBuilder $container): void
    {
        // TODO: Implement buildContainer() method.
    }

    protected function getCurrencyConverter()
    {
        $ecbProvider = new ECBProvider();
        $currencyConverter = new CurrencyConverter($ecbProvider);

        return $currencyConverter;
    }
}
