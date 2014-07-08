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

namespace Thelia\Form\Image;

use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Form\StandardDescriptionFieldsTrait;

/**
 * Created by JetBrains PhpStorm.
 * Date: 9/18/13
 * Time: 3:56 PM
 *
 * Form allowing to process a file
 *
 * @package File
 * @author  Guillaume MOREL <gmorel@openstudio.fr>
 *
 */
abstract class DocumentModification extends BaseForm
{
    use StandardDescriptionFieldsTrait;

    /**
     * @inheritdoc
     */
    protected function buildForm()
    {
        $this->formBuilder->add(
            'file',
            'file',
            [
                'required' => false,
                'constraints' => [ ],
                'label' => Translator::getInstance()->trans('Replace current document by this file'),
                'label_attr' => [
                    'for' => 'file'
                ]
            ]
        );

        // Add standard description fields
        $this->addStandardDescFields();

    }
}
