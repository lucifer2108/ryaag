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

namespace Thelia\Form;

use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Hook;
use Thelia\Model\HookQuery;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;

/**
 * Class HookCreationForm
 * @package Thelia\Form
 * @author Julien Chanséaume <jchanseaume@openstudio.fr>
 */
class ModuleHookCreationForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add("module_id", "choice", array(
                "choices" => $this->getModuleChoices(),
                "constraints" => array(
                    new NotBlank()
                ),
                "label" => Translator::getInstance()->trans("Module"),
                "label_attr" => array(
                    "for" => "module_id"
                )
            ))
            ->add("hook_id", "choice", array(
                "choices" => $this->getHookChoices(),
                "constraints" => array(
                    new NotBlank()
                ),
                "label" => Translator::getInstance()->trans("Hook"),
                "label_attr" => array("for" => "locale_create")
            ))
        ;
    }

    protected function getModuleChoices()
    {
        $choices = array();
        $modules = ModuleQuery::getActivatedAsc();
        /** @var Module $module */
        foreach ($modules as $module) {
            $choices[$module->getId()] = $module->getTitle();
        }

        return $choices;
    }

    protected function getHookChoices()
    {
        $choices = array();
        $hooks = HookQuery::create()
            ->filterByActivate(true, Criteria::EQUAL)
            ->find();
        /** @var Hook $hook */
        foreach ($hooks as $hook) {
            $choices[$hook->getId()] = $hook->getTitle();
        }

        return $choices;
    }

    public function getName()
    {
        return "thelia_module_hook_creation";
    }
}
