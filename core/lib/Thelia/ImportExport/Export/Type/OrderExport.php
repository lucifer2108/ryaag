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

namespace Thelia\ImportExport\Export\Type;

use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\ImportExport\Export\AbstractExport;
use Thelia\Model\Map\CountryI18nTableMap;
use Thelia\Model\Map\CurrencyTableMap;
use Thelia\Model\Map\CustomerTableMap;
use Thelia\Model\Map\CustomerTitleI18nTableMap;
use Thelia\Model\Map\OrderCouponTableMap;
use Thelia\Model\Map\OrderProductTableMap;
use Thelia\Model\Map\OrderProductTaxTableMap;
use Thelia\Model\Map\OrderStatusI18nTableMap;
use Thelia\Model\Map\OrderStatusTableMap;
use Thelia\Model\Map\OrderTableMap;
use Thelia\Model\Order;
use Thelia\Model\OrderQuery;
use Thelia\Tools\I18n;

/**
 * Class OrderExport
 *
 * @author Jérôme Billiras <jbilliras@openstudio.fr>
 */
class OrderExport extends AbstractExport
{
    const FILE_NAME = 'order';
    const USE_RANGE_DATE = true;

    protected $orderAndAliases = [
        OrderTableMap::COL_REF => 'ref',
        OrderTableMap::COL_CREATED_AT => 'date',
        'customer_REF' => 'customer_ref',
        OrderTableMap::COL_DISCOUNT => 'discount',
        'coupon_COUPONS' => 'coupons',
        OrderTableMap::COL_POSTAGE => 'postage',
        'order_TOTAL_TTC' => 'total_including_taxes',
        'order_TOTAL_WITH_DISCOUNT' => 'total_with_discount',
        'order_TOTAL_WITH_DISCOUNT_AND_POSTAGE' => 'total_discount_and_postage',
        'delivery_module_TITLE' => 'delivery_module',
        OrderTableMap::COL_DELIVERY_REF => 'delivery_ref',
        'payment_module_TITLE' => 'payment_module',
        OrderTableMap::COL_INVOICE_REF => 'invoice_ref',
        'order_status_TITLE' => 'status',
        'delivery_address_TITLE' => 'delivery_title',
        'delivery_address_COMPANY' => 'delivery_company',
        'delivery_address_FIRSTNAME' => 'delivery_first_name',
        'delivery_address_LASTNAME' => 'delivery_last_name',
        'delivery_address_ADDRESS1' => 'delivery_address1',
        'delivery_address_ADDRESS2' => 'delivery_address2',
        'delivery_address_ADDRESS3' => 'delivery_address3',
        'delivery_address_ZIPCODE' => 'delivery_zip_code',
        'delivery_address_CITY' => 'delivery_city',
        'delivery_address_country_TITLE' => 'delivery_country',
        'delivery_address_PHONE' => 'delivery_phone',
        'invoice_address_TITLE' => 'invoice_title',
        'invoice_address_COMPANY' => 'invoice_company',
        'invoice_address_FIRSTNAME' => 'invoice_first_name',
        'invoice_address_LASTNAME' => 'invoice_last_name',
        'invoice_address_ADDRESS1' => 'invoice_address1',
        'invoice_address_ADDRESS2' => 'invoice_address2',
        'invoice_address_ADDRESS3' => 'invoice_address3',
        'invoice_address_ZIPCODE' => 'invoice_zip_code',
        'invoice_address_CITY' => 'invoice_city',
        'invoice_address_country_TITLE' => 'invoice_country',
        'invoice_address_PHONE' => 'invoice_phone',
        'currency_CODE' => 'currency',
        'tax_TITLE' => 'tax_title'
    ];

    public function current()
    {
        $order = parent::current();

        $locale = $this->language->getLocale();

        $query = OrderQuery::create()
            ->useCurrencyQuery()
                ->addAsColumn('currency_CODE', CurrencyTableMap::COL_CODE)
                ->endUse()
            ->useCustomerQuery()
                ->addAsColumn('customer_REF', CustomerTableMap::COL_REF)
                ->endUse()
            ->useOrderProductQuery()
                ->useOrderProductTaxQuery(null, Criteria::LEFT_JOIN)
                    ->addAsColumn(
                        'product_TAX',
                        'IF('.OrderProductTableMap::COL_WAS_IN_PROMO.','.
                        'SUM('.OrderProductTaxTableMap::COL_PROMO_AMOUNT.'),'.
                        'SUM('.OrderProductTaxTableMap::COL_AMOUNT.')'.
                        ')'
                    )
                    ->addAsColumn('tax_TITLE', OrderProductTableMap::COL_TAX_RULE_TITLE)
                    ->endUse()
                ->addAsColumn('product_TITLE', OrderProductTableMap::COL_TITLE)
                ->addAsColumn(
                    'product_PRICE',
                    'IF('.OrderProductTableMap::COL_WAS_IN_PROMO.','.
                    OrderProductTableMap::COL_PROMO_PRICE .','.
                    OrderProductTableMap::COL_PRICE .
                    ')'
                )
                ->addAsColumn('product_QUANTITY', OrderProductTableMap::COL_QUANTITY)
                ->addAsColumn('product_WAS_IN_PROMO', OrderProductTableMap::COL_WAS_IN_PROMO)
                ->groupById()
                ->endUse()
            ->orderById()
            ->groupById()
            ->useOrderCouponQuery(null, Criteria::LEFT_JOIN)
                ->addAsColumn('coupon_COUPONS', 'GROUP_CONCAT('.OrderCouponTableMap::COL_TITLE.')')
                ->groupBy(OrderCouponTableMap::COL_ORDER_ID)
                ->endUse()
            ->useModuleRelatedByPaymentModuleIdQuery('payment_module')
                ->addAsColumn('payment_module_TITLE', '`payment_module`.CODE')
                ->endUse()
            ->useModuleRelatedByDeliveryModuleIdQuery('delivery_module')
                ->addAsColumn('delivery_module_TITLE', '`delivery_module`.CODE')
                ->endUse()
            ->useOrderAddressRelatedByDeliveryOrderAddressIdQuery('delivery_address_join')
                ->useCustomerTitleQuery('delivery_address_customer_title_join')
                    ->useCustomerTitleI18nQuery('delivery_address_customer_title_i18n_join')
                        ->addAsColumn('delivery_address_TITLE', '`delivery_address_customer_title_i18n_join`.SHORT')
                        ->endUse()
                    ->endUse()
                ->useCountryQuery('delivery_address_country_join')
                    ->useCountryI18nQuery('delivery_address_country_i18n_join')
                        ->addAsColumn('delivery_address_country_TITLE', '`delivery_address_country_i18n_join`.TITLE')
                        ->endUse()
                    ->addAsColumn('delivery_address_COMPANY', '`delivery_address_join`.COMPANY')
                    ->addAsColumn('delivery_address_FIRSTNAME', '`delivery_address_join`.FIRSTNAME')
                    ->addAsColumn('delivery_address_LASTNAME', '`delivery_address_join`.LASTNAME')
                    ->addAsColumn('delivery_address_ADDRESS1', '`delivery_address_join`.ADDRESS1')
                    ->addAsColumn('delivery_address_ADDRESS2', '`delivery_address_join`.ADDRESS2')
                    ->addAsColumn('delivery_address_ADDRESS3', '`delivery_address_join`.ADDRESS3')
                    ->addAsColumn('delivery_address_ZIPCODE', '`delivery_address_join`.ZIPCODE')
                    ->addAsColumn('delivery_address_CITY', '`delivery_address_join`.CITY')
                    ->addAsColumn('delivery_address_PHONE', '`delivery_address_join`.PHONE')
                    ->endUse()
                ->endUse()
            ->useOrderAddressRelatedByInvoiceOrderAddressIdQuery('invoice_address_join')
                ->useCustomerTitleQuery('invoice_address_customer_title_join')
                    ->useCustomerTitleI18nQuery('invoice_address_customer_title_i18n_join')
                        ->addAsColumn('invoice_address_TITLE', '`invoice_address_customer_title_i18n_join`.SHORT')
                        ->endUse()
                    ->endUse()
                ->useCountryQuery('invoice_address_country_join')
                    ->useCountryI18nQuery('invoice_address_country_i18n_join')
                        ->addAsColumn('invoice_address_country_TITLE', '`invoice_address_country_i18n_join`.TITLE')
                        ->endUse()
                    ->endUse()
                ->addAsColumn('invoice_address_COMPANY', '`invoice_address_join`.COMPANY')
                ->addAsColumn('invoice_address_FIRSTNAME', '`invoice_address_join`.FIRSTNAME')
                ->addAsColumn('invoice_address_LASTNAME', '`invoice_address_join`.LASTNAME')
                ->addAsColumn('invoice_address_ADDRESS1', '`invoice_address_join`.ADDRESS1')
                ->addAsColumn('invoice_address_ADDRESS2', '`invoice_address_join`.ADDRESS2')
                ->addAsColumn('invoice_address_ADDRESS3', '`invoice_address_join`.ADDRESS3')
                ->addAsColumn('invoice_address_ZIPCODE', '`invoice_address_join`.ZIPCODE')
                ->addAsColumn('invoice_address_CITY', '`invoice_address_join`.CITY')
                ->addAsColumn('invoice_address_PHONE', '`invoice_address_join`.PHONE')
                ->endUse()
            ->useOrderStatusQuery()
                ->useOrderStatusI18nQuery()
                    ->addAsColumn('order_status_TITLE', OrderStatusI18nTableMap::COL_TITLE)
                    ->endUse()
                ->endUse()
            ->select([
                OrderTableMap::COL_REF,
                'customer_REF',
                'product_TITLE',
                'product_PRICE',
                'product_TAX',
                'tax_TITLE',
                'product_QUANTITY',
                'product_WAS_IN_PROMO',
                OrderTableMap::COL_DISCOUNT,
                'coupon_COUPONS',
                OrderTableMap::COL_POSTAGE,
                'payment_module_TITLE',
                OrderTableMap::COL_INVOICE_REF,
                OrderTableMap::COL_DELIVERY_REF,
                'delivery_module_TITLE',
                'delivery_address_TITLE',
                'delivery_address_COMPANY',
                'delivery_address_FIRSTNAME',
                'delivery_address_LASTNAME',
                'delivery_address_ADDRESS1',
                'delivery_address_ADDRESS2',
                'delivery_address_ADDRESS3',
                'delivery_address_ZIPCODE',
                'delivery_address_CITY',
                'delivery_address_country_TITLE',
                'delivery_address_PHONE',
                'invoice_address_TITLE',
                'invoice_address_COMPANY',
                'invoice_address_FIRSTNAME',
                'invoice_address_LASTNAME',
                'invoice_address_ADDRESS1',
                'invoice_address_ADDRESS2',
                'invoice_address_ADDRESS3',
                'invoice_address_ZIPCODE',
                'invoice_address_CITY',
                'invoice_address_country_TITLE',
                'invoice_address_PHONE',
                'order_status_TITLE',
                'currency_CODE',
                OrderTableMap::COL_CREATED_AT,
            ])
            ->orderByCreatedAt(Criteria::DESC)
        ;

        I18n::addI18nCondition(
            $query,
            CustomerTitleI18nTableMap::TABLE_NAME,
            '`delivery_address_customer_title_join`.ID',
            CustomerTitleI18nTableMap::COL_ID,
            '`delivery_address_customer_title_i18n_join`.LOCALE',
            $locale
        );

        I18n::addI18nCondition(
            $query,
            CustomerTitleI18nTableMap::TABLE_NAME,
            '`invoice_address_customer_title_join`.ID',
            CustomerTitleI18nTableMap::COL_ID,
            '`invoice_address_customer_title_i18n_join`.LOCALE',
            $locale
        );

        I18n::addI18nCondition(
            $query,
            CountryI18nTableMap::TABLE_NAME,
            '`delivery_address_country_join`.ID',
            CountryI18nTableMap::COL_ID,
            '`delivery_address_country_i18n_join`.LOCALE',
            $locale
        );

        I18n::addI18nCondition(
            $query,
            CountryI18nTableMap::TABLE_NAME,
            '`invoice_address_country_join`.ID',
            CountryI18nTableMap::COL_ID,
            '`invoice_address_country_i18n_join`.LOCALE',
            $locale
        );

        I18n::addI18nCondition(
            $query,
            OrderStatusI18nTableMap::TABLE_NAME,
            OrderStatusI18nTableMap::COL_ID,
            OrderStatusTableMap::COL_ID,
            OrderStatusI18nTableMap::COL_LOCALE,
            $locale
        );

        $data = $query
            ->filterById($order[OrderTableMap::COL_ID])
            ->findOne();

        $order = (new Order)
            ->setId($order[OrderTableMap::COL_ID])
        ;
        $order->setNew(false);

        $tax = 0;
        $data['order_TOTAL_TTC'] = $order->getTotalAmount($tax, false, false);
        $data['order_TOTAL_WITH_DISCOUNT'] = $order->getTotalAmount($tax, false, true);
        $data['order_TOTAL_WITH_DISCOUNT_AND_POSTAGE'] = $order->getTotalAmount($tax, true, true);

        return $data;
    }

    protected function getData()
    {
        $orderQuery = new OrderQuery();

        if ($this->rangeDate !== null) {
            $orderQuery
                ->filterByCreatedAt($this->rangeDate['start'], Criteria::GREATER_EQUAL)
                ->filterByCreatedAt($this->rangeDate['end'], Criteria::LESS_EQUAL)
            ;
        }

        return $orderQuery;
    }
}
