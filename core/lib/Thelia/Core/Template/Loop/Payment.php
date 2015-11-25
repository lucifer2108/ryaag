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

use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Module\BaseModule;
use Thelia\Module\PaymentModuleInterface;

/**
 * Class Payment
 * @package Thelia\Core\Template\Loop
 * @author Etienne Roudeix <eroudeix@gmail.com>
 */
class Payment extends BaseSpecificModule implements PropelSearchLoopInterface
{
    protected $loopName = 'payment';

    public function getArgDefinitions()
    {
        $collection = parent::getArgDefinitions();

        return $collection;
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var \Thelia\Model\Module $paymentModule */
        foreach ($loopResult->getResultDataCollection() as $paymentModule) {
            $loopResultRow = new LoopResultRow($paymentModule);

            $moduleInstance = $paymentModule->getPaymentModuleInstance($this->container);

            if (false === $moduleInstance->isValidPayment()) {
                continue;
            }

            $loopResultRow
                ->set('ID', $paymentModule->getId())
                ->set('CODE', $paymentModule->getCode())
                ->set('TITLE', $paymentModule->getVirtualColumn('i18n_TITLE'))
                ->set('CHAPO', $paymentModule->getVirtualColumn('i18n_CHAPO'))
                ->set('DESCRIPTION', $paymentModule->getVirtualColumn('i18n_DESCRIPTION'))
                ->set('POSTSCRIPTUM', $paymentModule->getVirtualColumn('i18n_POSTSCRIPTUM'))
            ;
            $this->addOutputFields($loopResultRow, $paymentModule);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }

    protected function getModuleType()
    {
        return BaseModule::PAYMENT_MODULE_TYPE;
    }
}
