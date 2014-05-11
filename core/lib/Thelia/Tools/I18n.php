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

namespace Thelia\Tools;

use Thelia\Model\Lang;

/**
 * Created by JetBrains PhpStorm.
 * Date: 8/19/13
 * Time: 3:24 PM
 *
 * Helper for translations
 *
 * @package I18n
 * @author  Guillaume MOREL <gmorel@openstudio.fr>
 *
 */
class I18n
{
    /**
     * Create a \DateTime from a date picker form input
     * The date format is the same as the one from the current User Session
     * Ex : $lang = $session->getLang()
     *
     * @param Lang   $lang Object containing date format
     * @param string $date String to convert
     *
     * @return \DateTime
     */
    public function getDateTimeFromForm(Lang $lang, $date)
    {
        $currentDateFormat = $lang->getDateFormat();

        return \DateTime::createFromFormat($currentDateFormat, $date);
    }

    public static function forceI18nRetrieving($askedLocale, $modelName, $id, $needed = array('Title'))
    {
        $i18nQueryClass = sprintf("\\Thelia\\Model\\%sI18nQuery", $modelName);
        $i18nClass = sprintf("\\Thelia\\Model\\%sI18n", $modelName);

        /* get customer language translation */
        $i18n = $i18nQueryClass::create()
            ->filterById($id)
            ->filterByLocale(
                $askedLocale
            )->findOne();
        /* or default translation */
        if (null === $i18n) {
            $i18n = $i18nQueryClass::create()
                ->filterById($id)
                ->filterByLocale(
                    Lang::getDefaultLanguage()->getLocale()
                )->findOne();
        }
        if (null === $i18n) { // @todo something else ?
            $i18n = new $i18nClass();;
            $i18n->setId($id);
            foreach ($needed as $need) {
                $method = sprintf('set%s', $need);
                if (method_exists($i18n, $method)) {
                    $i18n->$method('DEFAULT ' . strtoupper($need));
                } else {
                    // @todo throw sg ?
                }
            }
        }

        return $i18n;
    }
}
