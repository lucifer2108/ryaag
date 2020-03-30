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

use PDO;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Propel\Runtime\Propel;
use Thelia\ImportExport\Export\JsonFileAbstractExport;

/**
 * Class ProductPricesExport
 * @package Thelia\ImportExport\Export\Type
 * @author Benjamin Perche <bperche@openstudio.fr>
 * @author Jérôme Billiras <jbilliras@openstudio.fr>
 * @contributor Thomas Arnaud <tarnaud@openstudio.fr>
 * @author Florian Bernard <fbernard@openstudio.fr>
 */
class ProductPricesExport extends JsonFileAbstractExport
{
    const FILE_NAME = 'product_price';

    protected $orderAndAliases = [
        'product_sale_elements_id' => 'id',
        'product_i18n_id' => 'product_id',
        'product_i18n_title' => 'product_title',
        'attribute_av_i18n_title' => 'attributes',
        'product_sale_elements_ean_code' => 'ean',
        'product_price_price' => 'price',
        'product_price_promo_price' => 'promo_price',
        'currency_code' => 'currency',
        'product_sale_elements_promo' => 'promo'
    ];

    protected function getData()
    {
        $locale = $this->language->getLocale();

        $con = Propel::getConnection();
        $query = 'SELECT 
                        product_sale_elements.id as "product_sale_elements_id",
                        product_i18n.id as "product_i18n_id",
                        product_i18n.title as "product_i18n_title",
                        attribute_av_i18n.title as "attribute_av_i18n_title",
                        product_sale_elements.ean_code as "product_sale_elements_ean_code",
                        ROUND(product_price.price, 2) as "product_price_price",
                        ROUND(product_price.promo_price, 2) as "product_price_promo_price",
                        currency.code as "currency_code",
                        product_sale_elements.promo as "product_sale_elements_promo"
                    FROM product_sale_elements
                    LEFT JOIN product_i18n ON product_i18n.id = product_sale_elements.product_id
                    LEFT JOIN attribute_combination ON attribute_combination.product_sale_elements_id = product_sale_elements.id
                    LEFT JOIN attribute_av_i18n ON attribute_av_i18n.id = attribute_combination.attribute_av_id AND attribute_av_i18n.locale = :locale
                    LEFT JOIN product_price ON product_price.product_sale_elements_id = product_sale_elements.id
                    LEFT JOIN currency ON currency.id = product_price.currency_id'
        ;

        $stmt = $con->prepare($query);
        $stmt->bindValue('locale', $locale);
        $stmt->execute();

        $filename = THELIA_CACHE_DIR . '/export/' . 'product_price.json';

        if (file_exists($filename)) {
            unlink($filename);
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            file_put_contents($filename, json_encode($row) . "\r\n", FILE_APPEND);
        }

        return $filename;
    }
}
