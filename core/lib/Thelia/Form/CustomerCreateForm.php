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

use Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\ExecutionContextInterface;
use Thelia\Model\ConfigQuery;
use Thelia\Model\CustomerQuery;
use Thelia\Core\Translation\Translator;

/**
 * Class CustomerCreateForm
 * @package Thelia\Form
 * @author Manuel Raynaud <mraynaud@openstudio.fr>
 */
class CustomerCreateForm extends AddressCreateForm
{

    protected function buildForm()
    {
        parent::buildForm();

        $this->formBuilder
            // Remove From Address create form
            ->remove("label")
            ->remove("is_default")

            // Add
            ->add("auto_login", "integer")
            // Add Email address
            ->add("email", "email", array(
                "constraints" => array(
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                    new Constraints\Callback(array(
                        "methods" => array(
                            array($this,
                                "verifyExistingEmail")
                        )
                    ))
                ),
                "label" => Translator::getInstance()->trans("Email Address"),
                "label_attr" => array(
                    "for" => "email"
                )
            ))
            // Add Login Information
            ->add("password", "password", array(
                "constraints" => array(
                    new Constraints\NotBlank(),
                    new Constraints\Length(array("min" => ConfigQuery::read("password.length", 4)))
                ),
                "label" => Translator::getInstance()->trans("Password"),
                "label_attr" => array(
                    "for" => "password"
                )
            ))
            ->add("password_confirm", "password", array(
                "constraints" => array(
                    new Constraints\NotBlank(),
                    new Constraints\Length(array("min" => ConfigQuery::read("password.length", 4))),
                    new Constraints\Callback(array("methods" => array(
                        array($this, "verifyPasswordField")
                    )))
                ),
                "label" => Translator::getInstance()->trans("Password confirmation"),
                "label_attr" => array(
                    "for" => "password_confirmation"
                )
            ))
            // Add Newsletter
            ->add("newsletter", "checkbox", array(
                "label" => Translator::getInstance()->trans('I would like to receive the newsletter or the latest news.'),
                "label_attr" => array(
                    "for" => "newsletter"
                ),
                "required" => false
            ))
            // Add terms & conditions
            ->add("agreed", "checkbox", array(
                "constraints" => array(
                    new Constraints\True(array("message" => Translator::getInstance()->trans("Please accept the Terms and conditions in order to register.")))
                ),
                "label"=>"Test",
                "label_attr" => array(
                    "for" => "agreed"
                )
            ));
    }

    public function verifyPasswordField($value, ExecutionContextInterface $context)
    {
        $data = $context->getRoot()->getData();

        if ($data["password"] != $data["password_confirm"]) {
            $context->addViolation(Translator::getInstance()->trans("password confirmation is not the same as password field"));
        }
    }

    public function verifyExistingEmail($value, ExecutionContextInterface $context)
    {
        $customer = CustomerQuery::getCustomerByEmail($value);
        if ($customer) {
            $context->addViolation(Translator::getInstance()->trans("This email already exists."));
        }
    }

    public function getName()
    {
        return "thelia_customer_create";
    }
}
