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

use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Template\Loop\Argument\Argument;

use Thelia\Type;
use Thelia\Core\Template\TemplateHelper;
use Thelia\Core\Template\TemplateDefinition;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\ArraySearchLoopInterface;

/**
 *
 * Template loop, to get available back-office or front-office templates.
 *
 * @package Thelia\Core\Template\Loop
 *
 * @author Franck Allimant <franck@cqfdev.fr>
 */
class Template extends BaseLoop implements ArraySearchLoopInterface
{
    /**
     * @return ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            new Argument(
                'template-type',
                new Type\TypeCollection(
                    new Type\EnumType(array(
                        'front-office',
                        'back-office',
                        'pdf',
                        'email'
                    ))
                )
            )
        );
    }

    public function buildArray()
    {
        $type = $this->getArg('template-type')->getValue();

        if ($type == 'front-office')
            $templateType = TemplateDefinition::FRONT_OFFICE;
        else if ($type == 'back-office')
            $templateType = TemplateDefinition::BACK_OFFICE;
        else if ($type == 'pdf')
            $templateType = TemplateDefinition::PDF;
        else if ($type == 'email')
            $templateType = TemplateDefinition::EMAIL;

        return TemplateHelper::getInstance()->getList($templateType);
    }

    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $template) {

            $loopResultRow = new LoopResultRow($template);

            $loopResultRow
                ->set("NAME"          , $template->getName())
                ->set("RELATIVE_PATH" , $template->getPath())
                ->set("ABSOLUTE_PATH" , $template->getAbsolutePath())
            ;

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
