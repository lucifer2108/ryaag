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

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Thelia\Core\Template\Element\BaseI18nLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;

use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Element\SearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Template\Loop\Argument\Argument;

use Thelia\Exception\TaxEngineException;
use Thelia\Model\CategoryQuery;
use Thelia\Model\ConfigQuery;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\Map\ProductPriceTableMap;
use Thelia\Model\Map\ProductSaleElementsTableMap;
use Thelia\Model\Map\ProductTableMap;
use Thelia\Model\ProductCategoryQuery;
use Thelia\Model\ProductQuery;

use Thelia\Type\TypeCollection;
use Thelia\Type;

/**
 *
 * Product loop
 *
 * Class Product
 * @package Thelia\Core\Template\Loop
 * @author Etienne Roudeix <eroudeix@openstudio.fr>
 */
class Product extends BaseI18nLoop implements PropelSearchLoopInterface, SearchLoopInterface
{
    protected $timestampable = true;
    protected $versionable = true;

    /**
     * @return ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createBooleanTypeArgument('complex', false),
            Argument::createIntListTypeArgument('id'),
            new Argument(
                'ref',
                new TypeCollection(
                    new Type\AlphaNumStringListType()
                )
            ),
            Argument::createIntListTypeArgument('category'),
            Argument::createIntListTypeArgument('category_default'),
            Argument::createBooleanTypeArgument('new'),
            Argument::createBooleanTypeArgument('promo'),
            Argument::createFloatTypeArgument('min_price'),
            Argument::createFloatTypeArgument('max_price'),
            Argument::createIntTypeArgument('min_stock'),
            Argument::createFloatTypeArgument('min_weight'),
            Argument::createFloatTypeArgument('max_weight'),
            Argument::createBooleanTypeArgument('with_prev_next_info', false),
            Argument::createBooleanTypeArgument('current'),
            Argument::createBooleanTypeArgument('current_category'),
            Argument::createIntTypeArgument('depth', 1),
            Argument::createBooleanOrBothTypeArgument('visible', 1),
            Argument::createIntTypeArgument('currency'),
            Argument::createAnyTypeArgument('title'),
            new Argument(
                'order',
                new TypeCollection(
                    new Type\EnumListType(array('id', 'id_reverse', 'alpha', 'alpha_reverse', 'min_price', 'max_price', 'manual', 'manual_reverse', 'ref', 'promo', 'new', 'random', 'given_id'))
                ),
                'alpha'
            ),
            Argument::createIntListTypeArgument('exclude'),
            Argument::createIntListTypeArgument('exclude_category'),
            new Argument(
                'feature_availability',
                new TypeCollection(
                    new Type\IntToCombinedIntsListType()
                )
            ),
            new Argument(
                'feature_values',
                new TypeCollection(
                    new Type\IntToCombinedStringsListType()
                )
            ),
            /*
             * promo, new, quantity, weight or price may differ depending on the different attributes
             * by default, product loop will look for at least 1 attribute which matches all the loop criteria : attribute_non_strict_match="none"
             * you can also provide a list of non-strict attributes.
             *      ie : attribute_non_strict_match="promo,new"
             *      loop will return the product if he has at least an attribute in promo and at least an attribute as new ; even if it's not the same attribute.
             * you can set all the attributes as non strict : attribute_non_strict_match="*"
             *
             * In order to allow such a process, we will have to make a LEFT JOIN foreach of the following case.
            */
            new Argument(
                'attribute_non_strict_match',
                new TypeCollection(
                    new Type\EnumListType(array('min_stock', 'promo', 'new', 'min_weight', 'max_weight', 'min_price', 'max_price')),
                    new Type\EnumType(array('*', 'none'))
                ),
                'none'
            )
        );
    }

    public function getSearchIn()
    {
        return array(
            "ref",
            "title",
        );
    }

    /**
     * @param ProductQuery $search
     * @param $searchTerm
     * @param $searchIn
     * @param $searchCriteria
     */
    public function doSearch(&$search, $searchTerm, $searchIn, $searchCriteria)
    {

        $search->_and();
        foreach ($searchIn as $index => $searchInElement) {
            if ($index > 0) {
                $search->_or();
            }
            switch ($searchInElement) {
                case "ref":
                    $search->filterByRef($searchTerm, $searchCriteria);
                    break;
                case "title":
                    $search->where("CASE WHEN NOT ISNULL(`requested_locale_i18n`.ID) THEN `requested_locale_i18n`.`TITLE` ELSE `default_locale_i18n`.`TITLE` END ".$searchCriteria." ?", $searchTerm, \PDO::PARAM_STR);
                    break;
            }
        }
    }

    public function buildModelCriteria()
    {
        $complex = $this->getComplex();
        if (true === $complex) {
            return $this->buildComplex();
        }

        $currencyId = $this->getCurrency();
        if (null !== $currencyId) {
            $currency = CurrencyQuery::create()->findOneById($currencyId);
            if (null === $currency) {
                throw new \InvalidArgumentException('Cannot found currency id: `' . $currency . '` in product_sale_elements loop');
            }
        } else {
            $currency = $this->request->getSession()->getCurrency();
        }

        $defaultCurrency = CurrencyQuery::create()->findOneByByDefault(1);
        $defaultCurrencySuffix = '_default_currency';

        $search = ProductQuery::create();
        $search->innerJoinProductSaleElements('pse');
        $search->addJoinCondition('pse', '`pse`.IS_DEFAULT=1');

        $priceJoin = new Join();
        $priceJoin->addExplicitCondition(ProductSaleElementsTableMap::TABLE_NAME, 'ID', 'pse', ProductPriceTableMap::TABLE_NAME, 'PRODUCT_SALE_ELEMENTS_ID', 'price');
        $priceJoin->setJoinType(Criteria::LEFT_JOIN);

        $search->addJoinObject($priceJoin, 'price_join')
            ->addJoinCondition('price_join', '`price`.`currency_id` = ?', $currency->getId(), null, \PDO::PARAM_INT);

        if ($defaultCurrency->getId() != $currency->getId()) {
            $priceJoinDefaultCurrency = new Join();
            $priceJoinDefaultCurrency->addExplicitCondition(ProductSaleElementsTableMap::TABLE_NAME, 'ID', 'pse', ProductPriceTableMap::TABLE_NAME, 'PRODUCT_SALE_ELEMENTS_ID', 'price' . $defaultCurrencySuffix);
            $priceJoinDefaultCurrency->setJoinType(Criteria::LEFT_JOIN);

            $search->addJoinObject($priceJoinDefaultCurrency, 'price_join' . $defaultCurrencySuffix)
                ->addJoinCondition('price_join' . $defaultCurrencySuffix, '`price' . $defaultCurrencySuffix . '`.`currency_id` = ?', $defaultCurrency->getId(), null, \PDO::PARAM_INT);

            /**
             * rate value is checked as a float in overloaded getRate method.
             */
            $priceToCompareAsSQL = 'CASE WHEN ISNULL(`price`.PRICE) OR `price`.FROM_DEFAULT_CURRENCY = 1 THEN
                    CASE WHEN `pse`.PROMO=1 THEN `price' . $defaultCurrencySuffix . '`.PROMO_PRICE ELSE `price' . $defaultCurrencySuffix . '`.PRICE END * ' . $currency->getRate() . '
                ELSE
                    CASE WHEN `pse`.PROMO=1 THEN `price`.PROMO_PRICE ELSE `price`.PRICE END
                END';

            $search->withColumn('ROUND(' . $priceToCompareAsSQL . ', 2)', 'real_price');
            $search->withColumn('CASE WHEN ISNULL(`price`.PRICE) OR `price`.FROM_DEFAULT_CURRENCY = 1 THEN `price' . $defaultCurrencySuffix . '`.PRICE * ' . $currency->getRate() . ' ELSE `price`.PRICE END', 'price');
            $search->withColumn('CASE WHEN ISNULL(`price`.PRICE) OR `price`.FROM_DEFAULT_CURRENCY = 1 THEN `price' . $defaultCurrencySuffix . '`.PROMO_PRICE * ' . $currency->getRate() . ' ELSE `price`.PROMO_PRICE END', 'promo_price');
        } else {
            $priceToCompareAsSQL = 'CASE WHEN `pse`.PROMO=1 THEN `price`.PROMO_PRICE ELSE `price`.PRICE END';

            $search->withColumn('ROUND(' . $priceToCompareAsSQL . ', 2)', 'real_price');
            $search->withColumn('`price`.PRICE', 'price');
            $search->withColumn('`price`.PROMO_PRICE', 'promo_price');
        }

        /* manage translations */
        $this->configureI18nProcessing($search, array('TITLE', 'CHAPO', 'DESCRIPTION', 'POSTSCRIPTUM', 'META_TITLE', 'META_DESCRIPTION', 'META_KEYWORDS'));

        $id = $this->getId();

        if (!is_null($id)) {
            $search->filterById($id, Criteria::IN);
        }

        $ref = $this->getRef();

        if (!is_null($ref)) {
            $search->filterByRef($ref, Criteria::IN);
        }

        $title = $this->getTitle();

        if (!is_null($title)) {
            $search->where("CASE WHEN NOT ISNULL(`requested_locale_i18n`.ID) THEN `requested_locale_i18n`.`TITLE` ELSE `default_locale_i18n`.`TITLE` END ".Criteria::LIKE." ?", "%".$title."%", \PDO::PARAM_STR);
        }

        $category = $this->getCategory();
        $categoryDefault = $this->getCategoryDefault();

        if (!is_null($category) ||!is_null($categoryDefault)) {

            $categoryIds = array();
            if (!is_array($category)) {
                $category = array();
            }
            if (!is_array($categoryDefault)) {
                $categoryDefault = array();
            }

            $categoryIds = array_merge($categoryIds, $category, $categoryDefault);
            $categories =CategoryQuery::create()->filterById($categoryIds, Criteria::IN)->find();

            $depth = $this->getDepth();

            if (null !== $depth) {
                foreach (CategoryQuery::findAllChild($category, $depth) as $subCategory) {
                    $categories->prepend($subCategory);
                }
            }

            $search->filterByCategory(
                $categories,
                Criteria::IN
            );
        }

        $new = $this->getNew();

        if ($new === true) {
            $search->where('`pse`.NEWNESS' . Criteria::EQUAL . '1');
        } elseif ($new === false) {
            $search->where('`pse`.NEWNESS' . Criteria::EQUAL . '0');
        }

        $promo = $this->getPromo();

        if ($promo === true) {
            $search->where('`pse`.PROMO' . Criteria::EQUAL . '1');
        } elseif ($promo === false) {
            $search->where('`pse`.PROMO' . Criteria::EQUAL . '0');
        }

        $min_stock = $this->getMin_stock();

        if (null != $min_stock) {
            $search->where('`pse`.QUANTITY' . Criteria::GREATER_THAN . '?', $min_stock, \PDO::PARAM_INT);
        }

        $min_weight = $this->getMin_weight();

        if (null != $min_weight) {
            $search->where('`pse`.WEIGHT' . Criteria::GREATER_THAN . '?', $min_weight, \PDO::PARAM_STR);
        }

        $max_weight = $this->getMax_weight();

        if (null != $max_weight) {
            $search->where('`is_max_weight`.WEIGHT' . Criteria::LESS_THAN . '?', $max_weight, \PDO::PARAM_STR);
        }

        $min_price = $this->getMin_price();

        if (null !== $min_price) {

            if (false === ConfigQuery::useTaxFreeAmounts()) {
                // @todo
            }

            $search->where('ROUND(' . $priceToCompareAsSQL . ', 2)>=?', $min_price, \PDO::PARAM_STR);
        }

        $max_price = $this->getMax_price();

        if (null !== $max_price) {
            if (false === ConfigQuery::useTaxFreeAmounts()) {
                // @todo
            }

            $search->where('ROUND(' . $priceToCompareAsSQL . ', 2)<=?', $max_price, \PDO::PARAM_STR);
        }

        $current = $this->getCurrent();

        if ($current === true) {
            $search->filterById($this->request->get("product_id"), Criteria::EQUAL);
        } elseif ($current === false) {
            $search->filterById($this->request->get("product_id"), Criteria::NOT_IN);
        }

        $current_category = $this->getCurrent_category();

        if ($current_category === true) {
            $search->filterByCategory(
                CategoryQuery::create()->filterByProduct(
                    ProductCategoryQuery::create()->filterByProductId(
                        $this->request->get("product_id"),
                        Criteria::EQUAL
                    )->find(),
                    Criteria::IN
                )->find(),
                Criteria::IN
            );
        } elseif ($current_category === false) {
            $search->filterByCategory(
                CategoryQuery::create()->filterByProduct(
                    ProductCategoryQuery::create()->filterByProductId(
                        $this->request->get("product_id"),
                        Criteria::EQUAL
                    )->find(),
                    Criteria::IN
                )->find(),
                Criteria::NOT_IN
            );
        }

        $visible = $this->getVisible();

        if ($visible !== Type\BooleanOrBothType::ANY) $search->filterByVisible($visible ? 1 : 0);

        $exclude = $this->getExclude();

        if (!is_null($exclude)) {
            $search->filterById($exclude, Criteria::NOT_IN);
        }

        $exclude_category = $this->getExclude_category();

        if (!is_null($exclude_category)) {
            $search->filterByCategory(
                CategoryQuery::create()->filterById($exclude_category, Criteria::IN)->find(),
                Criteria::NOT_IN
            );
        }

        $feature_availability = $this->getFeature_availability();

        $this->manageFeatureAv($search, $feature_availability);

        $feature_values = $this->getFeature_values();

        $this->manageFeatureValue($search, $feature_values);

        $search->groupBy(ProductTableMap::ID);

        $search->withColumn('`pse`.ID', 'pse_id');
        $search->withColumn('`pse`.NEWNESS', 'is_new');
        $search->withColumn('`pse`.PROMO', 'is_promo');
        $search->withColumn('`pse`.QUANTITY', 'quantity');
        $search->withColumn('`pse`.WEIGHT', 'weight');
        $search->withColumn('`pse`.EAN_CODE', 'ean_code');

        $orders  = $this->getOrder();

        foreach ($orders as $order) {
            switch ($order) {
                case "id":
                    $search->orderById(Criteria::ASC);
                    break;
                case "id_reverse":
                    $search->orderById(Criteria::DESC);
                    break;
                case "alpha":
                    $search->addAscendingOrderByColumn('i18n_TITLE');
                    break;
                case "alpha_reverse":
                    $search->addDescendingOrderByColumn('i18n_TITLE');
                    break;
                case "min_price":
                    $search->addAscendingOrderByColumn('real_price');
                    break;
                case "max_price":
                    $search->addDescendingOrderByColumn('real_price');
                    break;
                case "manual":
                    if(null === $categoryIds || count($categoryIds) != 1)
                        throw new \InvalidArgumentException('Manual order cannot be set without single category argument');
                    $search->orderByPosition(Criteria::ASC);
                    break;
                case "manual_reverse":
                    if(null === $categoryIds || count($categoryIds) != 1)
                        throw new \InvalidArgumentException('Manual order cannot be set without single category argument');
                    $search->orderByPosition(Criteria::DESC);
                    break;
                case "ref":
                    $search->orderByRef(Criteria::ASC);
                    break;
                case "promo":
                    $search->addDescendingOrderByColumn('is_promo');
                    break;
                case "new":
                    $search->addDescendingOrderByColumn('is_new');
                    break;
                case "given_id":
                    if(null === $id)
                        throw new \InvalidArgumentException('Given_id order cannot be set without `id` argument');
                    foreach ($id as $singleId) {
                        $givenIdMatched = 'given_id_matched_' . $singleId;
                        $search->withColumn(ProductTableMap::ID . "='$singleId'", $givenIdMatched);
                        $search->orderBy($givenIdMatched, Criteria::DESC);
                    }
                    break;
                case "random":
                    $search->clearOrderByColumns();
                    $search->addAscendingOrderByColumn('RAND()');
                    break(2);
            }
        }

        return $search;
    }

    public function parseResults(LoopResult $loopResult)
    {
        $complex = $this->getComplex();
        if (true === $complex) {
            return $this->parseComplex($loopResult);
        }

        $taxCountry = $this->container->get('thelia.taxEngine')->getDeliveryCountry();
        /** @var \Thelia\Core\Security\SecurityContext $securityContext */
        $securityContext = $this->container->get('thelia.securityContext');

        foreach ($loopResult->getResultDataCollection() as $product) {

            $loopResultRow = new LoopResultRow($product);

            $price = $product->getVirtualColumn('price');

            if ($securityContext->hasCustomerUser() && $securityContext->getCustomerUser()->getDiscount() > 0) {
                $price = $price * (1-($securityContext->getCustomerUser()->getDiscount()/100));
            }

            try {
                $taxedPrice = $product->getTaxedPrice(
                    $taxCountry,
                    $price
                );
            } catch (TaxEngineException $e) {
                $taxedPrice = null;
            }
            $promoPrice = $product->getVirtualColumn('promo_price');

            if ($securityContext->hasCustomerUser() && $securityContext->getCustomerUser()->getDiscount() > 0) {
                $promoPrice = $promoPrice * (1-($securityContext->getCustomerUser()->getDiscount()/100));
            }
            try {
                $taxedPromoPrice = $product->getTaxedPromoPrice(
                    $taxCountry,
                    $promoPrice
                );
            } catch (TaxEngineException $e) {
                $taxedPromoPrice = null;
            }

            // Find previous and next product, in the default category.
            $default_category_id = $product->getDefaultCategoryId();

            $loopResultRow
                ->set("WEIGHT"                  , $product->getVirtualColumn('weight'))
                ->set("QUANTITY"                , $product->getVirtualColumn('quantity'))
                ->set("EAN_CODE"                , $product->getVirtualColumn('ean_code'))
                ->set("BEST_PRICE"              , $product->getVirtualColumn('is_promo') ? $promoPrice : $price)
                ->set("BEST_PRICE_TAX"          , $taxedPrice - $product->getVirtualColumn('is_promo') ? $taxedPromoPrice - $promoPrice : $taxedPrice - $price)
                ->set("BEST_TAXED_PRICE"        , $product->getVirtualColumn('is_promo') ? $taxedPromoPrice : $taxedPrice)
                ->set("PRICE"                   , $price)
                ->set("PRICE_TAX"               , $taxedPrice - $price)
                ->set("TAXED_PRICE"             , $taxedPrice)
                ->set("PROMO_PRICE"             , $promoPrice)
                ->set("PROMO_PRICE_TAX"         , $taxedPromoPrice - $promoPrice)
                ->set("TAXED_PROMO_PRICE"       , $taxedPromoPrice)
                ->set("IS_PROMO"                , $product->getVirtualColumn('is_promo'))
                ->set("IS_NEW"                  , $product->getVirtualColumn('is_new'))

            ;


            $loopResult->addRow($this->associateValues($loopResultRow, $product, $default_category_id));
        }

        return $loopResult;
    }

    public function buildComplex()
    {
        $currencyId = $this->getCurrency();
        if (null !== $currencyId) {
            $currency = CurrencyQuery::create()->findOneById($currencyId);
            if (null === $currency) {
                throw new \InvalidArgumentException('Cannot found currency id: `' . $currency . '` in product_sale_elements loop');
            }
        } else {
            $currency = $this->request->getSession()->getCurrency();
        }

        $defaultCurrency = CurrencyQuery::create()->findOneByByDefault(1);
        $defaultCurrencySuffix = '_default_currency';

        $search = ProductQuery::create();

        /* manage translations */
        $this->configureI18nProcessing($search, array('TITLE', 'CHAPO', 'DESCRIPTION', 'POSTSCRIPTUM', 'META_TITLE', 'META_DESCRIPTION', 'META_KEYWORDS'));

        $attributeNonStrictMatch = $this->getAttribute_non_strict_match();
        $isPSELeftJoinList = array();
        $isProductPriceFirstLeftJoin = array();

        $id = $this->getId();

        if (!is_null($id)) {
            $search->filterById($id, Criteria::IN);
        }

        $ref = $this->getRef();

        if (!is_null($ref)) {
            $search->filterByRef($ref, Criteria::IN);
        }


        $title = $this->getTitle();

        if (!is_null($title)) {

            $search->where(" CASE WHEN NOT ISNULL(`requested_locale_i18n`.ID) THEN `requested_locale_i18n`.`TITLE` ELSE `default_locale_i18n`.`TITLE` END ".Criteria::LIKE." ?", "%".$title."%", \PDO::PARAM_STR);
        }




        $category = $this->getCategory();
        $categoryDefault = $this->getCategoryDefault();

        if (!is_null($category) ||!is_null($categoryDefault)) {

            $categoryIds = array();
            if (!is_array($category)) {
                $category = array();
            }
            if (!is_array($categoryDefault)) {
                $categoryDefault = array();
            }

            $categoryIds = array_merge($categoryIds, $category, $categoryDefault);
            $categories =CategoryQuery::create()->filterById($categoryIds, Criteria::IN)->find();

            $depth = $this->getDepth();

            if (null !== $depth) {
                foreach (CategoryQuery::findAllChild($category, $depth) as $subCategory) {
                    $categories->prepend($subCategory);
                }
            }

            $search->filterByCategory(
                $categories,
                Criteria::IN
            );
        }

        $new = $this->getNew();

        if ($new === true) {
            $isPSELeftJoinList[] = 'is_new';
            $search->joinProductSaleElements('is_new', Criteria::LEFT_JOIN)
                ->where('`is_new`.NEWNESS' . Criteria::EQUAL . '1')
                ->where('NOT ISNULL(`is_new`.ID)');
        } elseif ($new === false) {
            $isPSELeftJoinList[] = 'is_new';
            $search->joinProductSaleElements('is_new', Criteria::LEFT_JOIN)
                ->where('`is_new`.NEWNESS' . Criteria::EQUAL . '0')
                ->where('NOT ISNULL(`is_new`.ID)');
        }

        $promo = $this->getPromo();

        if ($promo === true) {
            $isPSELeftJoinList[] = 'is_promo';
            $search->joinProductSaleElements('is_promo', Criteria::LEFT_JOIN)
                ->where('`is_promo`.PROMO' . Criteria::EQUAL . '1')
                ->where('NOT ISNULL(`is_promo`.ID)');
        } elseif ($promo === false) {
            $isPSELeftJoinList[] = 'is_promo';
            $search->joinProductSaleElements('is_promo', Criteria::LEFT_JOIN)
                ->where('`is_promo`.PROMO' . Criteria::EQUAL . '0')
                ->where('NOT ISNULL(`is_promo`.ID)');
        }

        $min_stock = $this->getMin_stock();

        if (null != $min_stock) {
            $isPSELeftJoinList[] = 'is_min_stock';
            $search->joinProductSaleElements('is_min_stock', Criteria::LEFT_JOIN)
                ->where('`is_min_stock`.QUANTITY' . Criteria::GREATER_THAN . '?', $min_stock, \PDO::PARAM_INT)
                ->where('NOT ISNULL(`is_min_stock`.ID)');
        }

        $min_weight = $this->getMin_weight();

        if (null != $min_weight) {
            $isPSELeftJoinList[] = 'is_min_weight';
            $search->joinProductSaleElements('is_min_weight', Criteria::LEFT_JOIN)
                ->where('`is_min_weight`.WEIGHT' . Criteria::GREATER_THAN . '?', $min_weight, \PDO::PARAM_STR)
                ->where('NOT ISNULL(`is_min_weight`.ID)');
        }

        $max_weight = $this->getMax_weight();

        if (null != $max_weight) {
            $isPSELeftJoinList[] = 'is_max_weight';
            $search->joinProductSaleElements('is_max_weight', Criteria::LEFT_JOIN)
                ->where('`is_max_weight`.WEIGHT' . Criteria::LESS_THAN . '?', $max_weight, \PDO::PARAM_STR)
                ->where('NOT ISNULL(`is_max_weight`.ID)');
        }

        if ($attributeNonStrictMatch != '*') {
            if ($attributeNonStrictMatch == 'none') {
                $actuallyUsedAttributeNonStrictMatchList = $isPSELeftJoinList;
            } else {
                $actuallyUsedAttributeNonStrictMatchList = array_values(array_intersect($isPSELeftJoinList, $attributeNonStrictMatch));
            }

            foreach ($actuallyUsedAttributeNonStrictMatchList as $key => $actuallyUsedAttributeNonStrictMatch) {
                if($key == 0)
                    continue;
                $search->where('`' . $actuallyUsedAttributeNonStrictMatch . '`.ID=' . '`' . $actuallyUsedAttributeNonStrictMatchList[$key-1] . '`.ID');
            }
        }

        $min_price = $this->getMin_price();

        if (null !== $min_price) {

            if (false === ConfigQuery::useTaxFreeAmounts()) {
                // @todo
            }

            $isPSELeftJoinList[] = 'is_min_price';
            $isProductPriceFirstLeftJoin = array('is_min_price', 'min_price_data');

            $minPriceJoin = new Join();
            $minPriceJoin->addExplicitCondition(ProductSaleElementsTableMap::TABLE_NAME, 'ID', 'is_min_price', ProductPriceTableMap::TABLE_NAME, 'PRODUCT_SALE_ELEMENTS_ID', 'min_price_data');
            $minPriceJoin->setJoinType(Criteria::LEFT_JOIN);

            $search->joinProductSaleElements('is_min_price', Criteria::LEFT_JOIN)
                ->addJoinObject($minPriceJoin, 'is_min_price_join')
                ->addJoinCondition('is_min_price_join', '`min_price_data`.`currency_id` = ?', $currency->getId(), null, \PDO::PARAM_INT);

            if ($defaultCurrency->getId() != $currency->getId()) {
                $minPriceJoinDefaultCurrency = new Join();
                $minPriceJoinDefaultCurrency->addExplicitCondition(ProductSaleElementsTableMap::TABLE_NAME, 'ID', 'is_min_price', ProductPriceTableMap::TABLE_NAME, 'PRODUCT_SALE_ELEMENTS_ID', 'min_price_data' . $defaultCurrencySuffix);
                $minPriceJoinDefaultCurrency->setJoinType(Criteria::LEFT_JOIN);

                $search->addJoinObject($minPriceJoinDefaultCurrency, 'is_min_price_join' . $defaultCurrencySuffix)
                    ->addJoinCondition('is_min_price_join' . $defaultCurrencySuffix, '`min_price_data' . $defaultCurrencySuffix . '`.`currency_id` = ?', $defaultCurrency->getId(), null, \PDO::PARAM_INT);

                /**
                 * In propel we trust : $currency->getRate() always returns a float.
                 * Or maybe not : rate value is checked as a float in overloaded getRate method.
                 */
                $MinPriceToCompareAsSQL = 'CASE WHEN ISNULL(CASE WHEN `is_min_price`.PROMO=1 THEN `min_price_data`.PROMO_PRICE ELSE `min_price_data`.PRICE END) OR `min_price_data`.FROM_DEFAULT_CURRENCY = 1 THEN
                    CASE WHEN `is_min_price`.PROMO=1 THEN `min_price_data' . $defaultCurrencySuffix . '`.PROMO_PRICE ELSE `min_price_data' . $defaultCurrencySuffix . '`.PRICE END * ' . $currency->getRate() . '
                ELSE
                    CASE WHEN `is_min_price`.PROMO=1 THEN `min_price_data`.PROMO_PRICE ELSE `min_price_data`.PRICE END
                END';
            } else {
                $MinPriceToCompareAsSQL = 'CASE WHEN `is_min_price`.PROMO=1 THEN `min_price_data`.PROMO_PRICE ELSE `min_price_data`.PRICE END';
            }

            $search->where('ROUND(' . $MinPriceToCompareAsSQL . ', 2)>=?', $min_price, \PDO::PARAM_STR);
        }

        $max_price = $this->getMax_price();

        if (null !== $max_price) {
            $isPSELeftJoinList[] = 'is_max_price';
            $isProductPriceFirstLeftJoin = array('is_max_price', 'max_price_data');

            $maxPriceJoin = new Join();
            $maxPriceJoin->addExplicitCondition(ProductSaleElementsTableMap::TABLE_NAME, 'ID', 'is_max_price', ProductPriceTableMap::TABLE_NAME, 'PRODUCT_SALE_ELEMENTS_ID', 'max_price_data');
            $maxPriceJoin->setJoinType(Criteria::LEFT_JOIN);

            $search->joinProductSaleElements('is_max_price', Criteria::LEFT_JOIN)
                ->addJoinObject($maxPriceJoin, 'is_max_price_join')
                ->addJoinCondition('is_max_price_join', '`max_price_data`.`currency_id` = ?', $currency->getId(), null, \PDO::PARAM_INT);

            if ($defaultCurrency->getId() != $currency->getId()) {
                $maxPriceJoinDefaultCurrency = new Join();
                $maxPriceJoinDefaultCurrency->addExplicitCondition(ProductSaleElementsTableMap::TABLE_NAME, 'ID', 'is_max_price', ProductPriceTableMap::TABLE_NAME, 'PRODUCT_SALE_ELEMENTS_ID', 'max_price_data' . $defaultCurrencySuffix);
                $maxPriceJoinDefaultCurrency->setJoinType(Criteria::LEFT_JOIN);

                $search->addJoinObject($maxPriceJoinDefaultCurrency, 'is_max_price_join' . $defaultCurrencySuffix)
                    ->addJoinCondition('is_max_price_join' . $defaultCurrencySuffix, '`max_price_data' . $defaultCurrencySuffix . '`.`currency_id` = ?', $defaultCurrency->getId(), null, \PDO::PARAM_INT);

                /**
                 * In propel we trust : $currency->getRate() always returns a float.
                 * Or maybe not : rate value is checked as a float in overloaded getRate method.
                 */
                $MaxPriceToCompareAsSQL = 'CASE WHEN ISNULL(CASE WHEN `is_max_price`.PROMO=1 THEN `max_price_data`.PROMO_PRICE ELSE `max_price_data`.PRICE END) OR `min_price_data`.FROM_DEFAULT_CURRENCY = 1 THEN
                    CASE WHEN `is_max_price`.PROMO=1 THEN `max_price_data' . $defaultCurrencySuffix . '`.PROMO_PRICE ELSE `max_price_data' . $defaultCurrencySuffix . '`.PRICE END * ' . $currency->getRate() . '
                ELSE
                    CASE WHEN `is_max_price`.PROMO=1 THEN `max_price_data`.PROMO_PRICE ELSE `max_price_data`.PRICE END
                END';

            } else {
                $MaxPriceToCompareAsSQL = 'CASE WHEN `is_max_price`.PROMO=1 THEN `max_price_data`.PROMO_PRICE ELSE `max_price_data`.PRICE END';
            }

            $search->where('ROUND(' . $MaxPriceToCompareAsSQL . ', 2)<=?', $max_price, \PDO::PARAM_STR);
        }

        /*
         * for ordering and outputs, the product will be :
         * - new if at least one the criteria matching PSE is new
         * - in promo if at least one the criteria matching PSE is in promo
         */

        /* if we don't have any join yet, let's make a global one */
        if (empty($isProductPriceFirstLeftJoin)) {
            if (count($isPSELeftJoinList) == 0) {
                $joiningTable = "global";
                $isPSELeftJoinList[] = $joiningTable;
                $search->joinProductSaleElements('global', Criteria::LEFT_JOIN);
            } else {
                $joiningTable = $isPSELeftJoinList[0];
            }

            $isProductPriceFirstLeftJoin = array($joiningTable, 'global_price_data');

            $globalPriceJoin = new Join();
            $globalPriceJoin->addExplicitCondition(ProductSaleElementsTableMap::TABLE_NAME, 'ID', $joiningTable, ProductPriceTableMap::TABLE_NAME, 'PRODUCT_SALE_ELEMENTS_ID', 'global_price_data');
            $globalPriceJoin->setJoinType(Criteria::LEFT_JOIN);

            $search->addJoinObject($globalPriceJoin, 'global_price_join')
                ->addJoinCondition('global_price_join', '`global_price_data`.`currency_id` = ?', $currency->getId(), null, \PDO::PARAM_INT);

            if ($defaultCurrency->getId() != $currency->getId()) {
                $globalPriceJoinDefaultCurrency = new Join();
                $globalPriceJoinDefaultCurrency->addExplicitCondition(ProductSaleElementsTableMap::TABLE_NAME, 'ID', $joiningTable, ProductPriceTableMap::TABLE_NAME, 'PRODUCT_SALE_ELEMENTS_ID', 'global_price_data' . $defaultCurrencySuffix);
                $globalPriceJoinDefaultCurrency->setJoinType(Criteria::LEFT_JOIN);
                $search->addJoinObject($globalPriceJoinDefaultCurrency, 'global_price_join' . $defaultCurrencySuffix)
                    ->addJoinCondition('global_price_join' . $defaultCurrencySuffix, '`global_price_data' . $defaultCurrencySuffix . '`.`currency_id` = ?', $defaultCurrency->getId(), null, \PDO::PARAM_INT);
            }
        }

        /*
         * we need to test all promo field from our previous conditions. Indeed ie:
         * product P0, attributes color : red
         * P0red is in promo and is the only attribute combinaton availability.
         * so the product might be consider as in promo (in outputs and ordering)
         * We got the following loop to display in promo AND new product but we don't care it's the same attribute which is new and in promo :
         * {loop type="product" promo="1" new="1" attribute_non_strict_match="promo,new"} {/loop}
         * our request will so far returns 1 line
         *
         * is_promo.ID | is_promo.PROMO | is_promo.NEWNESS | is_new.ID | is_new.PROMO | is_new.NEWNESS
         *      NULL            NULL              NULL        red_id         1               0
         *
         * So that we can say the product is in global promo only with is_promo.PROMO, we must acknowledge it with (is_promo.PROMO OR is_new.PROMO)
         */
        $booleanMatchedPromoList = array();
        $booleanMatchedNewnessList = array();
        foreach ($isPSELeftJoinList as $isPSELeftJoin) {
            $booleanMatchedPromoList[] = '`' . $isPSELeftJoin . '`.PROMO';
            $booleanMatchedNewnessList[] = '`' . $isPSELeftJoin . '`.NEWNESS';
        }
        $search->withColumn('ROUND(MAX(' . implode(' OR ', $booleanMatchedPromoList) . '), 2)', 'main_product_is_promo');
        $search->withColumn('ROUND(MAX(' . implode(' OR ', $booleanMatchedNewnessList) . '), 2)', 'main_product_is_new');

        $booleanMatchedPrice = 'CASE WHEN `' . $isProductPriceFirstLeftJoin[0] . '`.PROMO=1 THEN `' . $isProductPriceFirstLeftJoin[1] . '`.PROMO_PRICE ELSE `' . $isProductPriceFirstLeftJoin[1] . '`.PRICE END';
        $booleanMatchedPriceDefaultCurrency = 'CASE WHEN `' . $isProductPriceFirstLeftJoin[0] . '`.PROMO=1 THEN `' . $isProductPriceFirstLeftJoin[1] . $defaultCurrencySuffix . '`.PROMO_PRICE ELSE `' . $isProductPriceFirstLeftJoin[1] . $defaultCurrencySuffix . '`.PRICE END';

        if ($defaultCurrency->getId() != $currency->getId()) {
            /**
             * In propel we trust : $currency->getRate() always returns a float.
             * Or maybe not : rate value is checked as a float in overloaded getRate method.
             */
            $priceToCompareAsSQL = 'CASE WHEN ISNULL(' . $booleanMatchedPrice .') THEN ' . $booleanMatchedPriceDefaultCurrency . ' * ' . $currency->getRate() . ' ELSE ' . $booleanMatchedPrice . ' END';
        } else {
            $priceToCompareAsSQL = $booleanMatchedPrice;
        }

        $search->withColumn('ROUND(MAX(' . $priceToCompareAsSQL . '), 2)', 'real_highest_price');
        $search->withColumn('ROUND(MIN(' . $priceToCompareAsSQL . '), 2)', 'real_lowest_price');

        $current = $this->getCurrent();

        if ($current === true) {
            $search->filterById($this->request->get("product_id"));
        } elseif ($current === false) {
            $search->filterById($this->request->get("product_id"), Criteria::NOT_IN);
        }

        $current_category = $this->getCurrent_category();

        if ($current_category === true) {
            $search->filterByCategory(
                CategoryQuery::create()->filterByProduct(
                    ProductCategoryQuery::create()->filterByProductId(
                        $this->request->get("product_id"),
                        Criteria::EQUAL
                    )->find(),
                    Criteria::IN
                )->find(),
                Criteria::IN
            );
        } elseif ($current_category === false) {
            $search->filterByCategory(
                CategoryQuery::create()->filterByProduct(
                    ProductCategoryQuery::create()->filterByProductId(
                        $this->request->get("product_id"),
                        Criteria::EQUAL
                    )->find(),
                    Criteria::IN
                )->find(),
                Criteria::NOT_IN
            );
        }

        $visible = $this->getVisible();

        if ($visible !== Type\BooleanOrBothType::ANY) $search->filterByVisible($visible ? 1 : 0);

        $exclude = $this->getExclude();

        if (!is_null($exclude)) {
            $search->filterById($exclude, Criteria::NOT_IN);
        }

        $exclude_category = $this->getExclude_category();

        if (!is_null($exclude_category)) {
            $search->filterByCategory(
                CategoryQuery::create()->filterById($exclude_category, Criteria::IN)->find(),
                Criteria::NOT_IN
            );
        }

        $feature_availability = $this->getFeature_availability();

        $this->manageFeatureAv($search, $feature_availability);

        $feature_values = $this->getFeature_values();

        $this->manageFeatureValue($search, $feature_values);

        $search->groupBy(ProductTableMap::ID);

        $orders  = $this->getOrder();

        foreach ($orders as $order) {
            switch ($order) {
                case "id":
                    $search->orderById(Criteria::ASC);
                    break;
                case "id_reverse":
                    $search->orderById(Criteria::DESC);
                    break;
                case "alpha":
                    $search->addAscendingOrderByColumn('i18n_TITLE');
                    break;
                case "alpha_reverse":
                    $search->addDescendingOrderByColumn('i18n_TITLE');
                    break;
                case "min_price":
                    $search->addAscendingOrderByColumn('real_lowest_price', Criteria::ASC);
                    break;
                case "max_price":
                    $search->addDescendingOrderByColumn('real_lowest_price');
                    break;
                case "manual":
                    if(null === $categoryIds || count($categoryIds) != 1)
                        throw new \InvalidArgumentException('Manual order cannot be set without single category argument');
                    $search->orderByPosition(Criteria::ASC);
                    break;
                case "manual_reverse":
                    if(null === $categoryIds || count($categoryIds) != 1)
                        throw new \InvalidArgumentException('Manual order cannot be set without single category argument');
                    $search->orderByPosition(Criteria::DESC);
                    break;
                case "ref":
                    $search->orderByRef(Criteria::ASC);
                    break;
                case "promo":
                    $search->addDescendingOrderByColumn('main_product_is_promo');
                    break;
                case "new":
                    $search->addDescendingOrderByColumn('main_product_is_new');
                    break;
                case "given_id":
                    if(null === $id)
                        throw new \InvalidArgumentException('Given_id order cannot be set without `id` argument');
                    foreach ($id as $singleId) {
                        $givenIdMatched = 'given_id_matched_' . $singleId;
                        $search->withColumn(ProductTableMap::ID . "='$singleId'", $givenIdMatched);
                        $search->orderBy($givenIdMatched, Criteria::DESC);
                    }
                    break;
                case "random":
                    $search->clearOrderByColumns();
                    $search->addAscendingOrderByColumn('RAND()');
                    break(2);
            }
        }

        return $search;
    }

    public function parseComplex(LoopResult $results)
    {
        $loopResult = new LoopResult($results);

        $taxCountry = $this->container->get('thelia.taxEngine')->getDeliveryCountry();
        /** @var \Thelia\Core\Security\SecurityContext $securityContext */
        $securityContext = $this->container->get('thelia.securityContext');

        foreach ($loopResult->getResultDataCollection() as $product) {

            $loopResultRow = new LoopResultRow($product);

            $price = $product->getRealLowestPrice();

           if ($securityContext->hasCustomerUser() && $securityContext->getCustomerUser()->getDiscount() > 0) {
                $price = $price * (1-($securityContext->getCustomerUser()->getDiscount()/100));
            }

            try {
                $taxedPrice = $product->getTaxedPrice(
                    $taxCountry,
                    $price
                );
            } catch (TaxEngineException $e) {
                $taxedPrice = null;
            }

            // Find previous and next product, in the default category.
            $default_category_id = $product->getDefaultCategoryId();

            $loopResultRow
                ->set("BEST_PRICE"       , $price)
                ->set("BEST_PRICE_TAX"   , $taxedPrice - $price)
                ->set("BEST_TAXED_PRICE" , $taxedPrice)
                ->set("IS_PROMO"                , $product->getVirtualColumn('is_promo'))
                ->set("IS_NEW"                  , $product->getVirtualColumn('is_new'))
            ;


            $loopResult->addRow($this->associateValues($loopResultRow, $product, $default_category_id));
        }

        return $loopResult;
    }

    private function associateValues($loopResultRow, $product, $default_category_id)
    {
        $loopResultRow
            ->set("ID"                      , $product->getId())
            ->set("REF"                     , $product->getRef())
            ->set("IS_TRANSLATED"           , $product->getVirtualColumn('IS_TRANSLATED'))
            ->set("LOCALE"                  , $this->locale)
            ->set("TITLE"                   , $product->getVirtualColumn('i18n_TITLE'))
            ->set("CHAPO"                   , $product->getVirtualColumn('i18n_CHAPO'))
            ->set("DESCRIPTION"             , $product->getVirtualColumn('i18n_DESCRIPTION'))
            ->set("POSTSCRIPTUM"            , $product->getVirtualColumn('i18n_POSTSCRIPTUM'))
            ->set("URL"                     , $product->getUrl($this->locale))
            ->set("META_TITLE"              , $product->getVirtualColumn('i18n_META_TITLE'))
            ->set("META_DESCRIPTION"        , $product->getVirtualColumn('i18n_META_DESCRIPTION'))
            ->set("META_KEYWORDS"            , $product->getVirtualColumn('i18n_META_KEYWORDS'))
            ->set("PRODUCT_SALE_ELEMENT"    , $product->getVirtualColumn('pse_id'))
            ->set("POSITION"                , $product->getPosition())
            ->set("VISIBLE"                 , $product->getVisible() ? "1" : "0")
            ->set("TEMPLATE"                , $product->getTemplateId())
            ->set("DEFAULT_CATEGORY"        , $default_category_id)
            ->set("TAX_RULE_ID"             , $product->getTaxRuleId())

        ;


        if ($this->getBackend_context() || $this->getWithPrevNextInfo()) {
            // Find previous and next category
            $previous = ProductQuery::create()
                ->joinProductCategory()
                ->where('ProductCategory.category_id = ?', $default_category_id)
                ->filterByPosition($product->getPosition(), Criteria::LESS_THAN)
                ->orderByPosition(Criteria::DESC)
                ->findOne()
            ;

            $next = ProductQuery::create()
                ->joinProductCategory()
                ->where('ProductCategory.category_id = ?', $default_category_id)
                ->filterByPosition($product->getPosition(), Criteria::GREATER_THAN)
                ->orderByPosition(Criteria::ASC)
                ->findOne()
            ;

            $loopResultRow
                ->set("HAS_PREVIOUS"     , $previous != null ? 1 : 0)
                ->set("HAS_NEXT"         , $next != null ? 1 : 0)
                ->set("PREVIOUS"         , $previous != null ? $previous->getId() : -1)
                ->set("NEXT"             , $next != null ? $next->getId() : -1)
            ;
        }

        return $loopResultRow;
    }

    protected function manageFeatureAv(&$search, $feature_availability)
    {
        if (null !== $feature_availability) {
            foreach ($feature_availability as $feature => $feature_choice) {
                foreach ($feature_choice['values'] as $feature_av) {
                    $featureAlias = 'fa_' . $feature;
                    if($feature_av != '*')
                        $featureAlias .= '_' . $feature_av;
                    $search->joinFeatureProduct($featureAlias, Criteria::LEFT_JOIN)
                        ->addJoinCondition($featureAlias, "`$featureAlias`.FEATURE_ID = ?", $feature, null, \PDO::PARAM_INT);
                    if($feature_av != '*')
                        $search->addJoinCondition($featureAlias, "`$featureAlias`.FEATURE_AV_ID = ?", $feature_av, null, \PDO::PARAM_INT);
                }

                /* format for mysql */
                $sqlWhereString = $feature_choice['expression'];
                if ($sqlWhereString == '*') {
                    $sqlWhereString = 'NOT ISNULL(`fa_' . $feature . '`.ID)';
                } else {
                    $sqlWhereString = preg_replace('#([0-9]+)#', 'NOT ISNULL(`fa_' . $feature . '_' . '\1`.ID)', $sqlWhereString);
                    $sqlWhereString = str_replace('&', ' AND ', $sqlWhereString);
                    $sqlWhereString = str_replace('|', ' OR ', $sqlWhereString);
                }

                $search->where("(" . $sqlWhereString . ")");
            }
        }
    }

    protected function manageFeatureValue(&$search, $feature_values)
    {
        if (null !== $feature_values) {
            foreach ($feature_values as $feature => $feature_choice) {
                foreach ($feature_choice['values'] as $feature_value) {
                    $featureAlias = 'fv_' . $feature;
                    if($feature_value != '*')
                        $featureAlias .= '_' . $feature_value;
                    $search->joinFeatureProduct($featureAlias, Criteria::LEFT_JOIN)
                        ->addJoinCondition($featureAlias, "`$featureAlias`.FEATURE_ID = ?", $feature, null, \PDO::PARAM_INT);
                    if($feature_value != '*')
                        $search->addJoinCondition($featureAlias, "`$featureAlias`.BY_DEFAULT = ?", $feature_value, null, \PDO::PARAM_STR);
                }

                /* format for mysql */
                $sqlWhereString = $feature_choice['expression'];
                if ($sqlWhereString == '*') {
                    $sqlWhereString = 'NOT ISNULL(`fv_' . $feature . '`.ID)';
                } else {
                    $sqlWhereString = preg_replace('#([a-zA-Z0-9_\-]+)#', 'NOT ISNULL(`fv_' . $feature . '_' . '\1`.ID)', $sqlWhereString);
                    $sqlWhereString = str_replace('&', ' AND ', $sqlWhereString);
                    $sqlWhereString = str_replace('|', ' OR ', $sqlWhereString);
                }

                $search->where("(" . $sqlWhereString . ")");
            }
        }
    }
}
