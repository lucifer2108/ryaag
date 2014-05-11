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

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Thelia\Core\Translation\Translator;

class MessageModificationForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add("id"           , "hidden", array("constraints" => array(new GreaterThan(array('value' => 0)))))
            ->add("name"         , "text"  , array(
                "constraints" => array(new NotBlank()),
                "label" => Translator::getInstance()->trans('Name'),
                "label_attr" => array(
                    "for" => "name"
                ),
                "required" => true
            ))
            ->add("secured"      , "text"  , array(
                "label" => Translator::getInstance()->trans('Prevent mailing template modification or deletion, except for super-admin')
            ))
            ->add("locale"       , "text"  , array())
            ->add("title"        , "text"  , array(
                "constraints" => array(new NotBlank()),
                "label" => Translator::getInstance()->trans('Title'),
                "label_attr" => array(
                    "for" => "title"
                ),
                "required" => true
            ))
            ->add("subject"      , "text"  , array(
                "constraints" => array(new NotBlank()),
                "label" => Translator::getInstance()->trans('Message subject'),
                "label_attr" => array(
                    "for" => "subject"
                ),
                "required" => true
            ))
            ->add("html_message" , "text"  , array(
                "label" => Translator::getInstance()->trans('HTML Message'),
                "label_attr" => array(
                    "for" => "html_message"
                ),
                "required" => false
            ))
            ->add("text_message" , "text"  , array(
                "label" => Translator::getInstance()->trans('Text Message'),
                "label_attr" => array(
                    "for" => "text_message"
                ),
                "required" => false
            ))
            ->add("html_layout_file_name" , "text"  , array(
                    "label" => Translator::getInstance()->trans('Name of the HTML layout file'),
                    "label_attr" => array(
                            "for" => "html_layout_file_name"
                ),
                "required" => false
            ))
            ->add("html_template_file_name" , "text"  , array(
                    "label" => Translator::getInstance()->trans('Name of the HTML template file'),
                    "label_attr" => array(
                            "for" => "html_template_file_name"
                ),
                "required" => false
            ))
            ->add("text_layout_file_name" , "text"  , array(
                    "label" => Translator::getInstance()->trans('Name of the text layout file'),
                    "label_attr" => array(
                            "for" => "text_layout_file_name"
                ),
                "required" => false
            ))
            ->add("text_template_file_name" , "text"  , array(
                    "label" => Translator::getInstance()->trans('Name of the text template file'),
                    "label_attr" => array(
                            "for" => "text_template_file_name"
                ),
                "required" => false
            ))
            ;
    }

    public function getName()
    {
        return "thelia_message_modification";
    }
}
