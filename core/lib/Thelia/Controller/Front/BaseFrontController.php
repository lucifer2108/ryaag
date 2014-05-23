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

namespace Thelia\Controller\Front;

use Symfony\Component\Routing\Router;
use Thelia\Controller\BaseController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Template\TemplateDefinition;
use Thelia\Core\Template\TemplateHelper;
use Thelia\Model\AddressQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Tools\URL;

class BaseFrontController extends BaseController
{
    /**
     * Return the route path defined for the givent route ID
     *
     * @param string $routeId a route ID, as defines in Config/Resources/routing/front.xml
     *
     * @see \Thelia\Controller\BaseController::getRouteFromRouter()
     */
    protected function getRoute($routeId, $parameters = array(), $referenceType = Router::ABSOLUTE_PATH)
    {
        return $this->getRouteFromRouter('router.front', $routeId, $parameters, $referenceType);
    }

    /**
     * Redirect to à route ID related URL
     *
     * @param string $routeId       the route ID, as found in Config/Resources/routing/admin.xml
     * @param array  $urlParameters the URL parametrs, as a var/value pair array
     * @param bool   $referenceType
     */
    public function redirectToRoute($routeId, array $urlParameters = [], array $routeParameters = [], $referenceType = Router::ABSOLUTE_PATH)
    {
        $this->redirect(URL::getInstance()->absoluteUrl($this->getRoute($routeId, $routeParameters, $referenceType), $urlParameters));
    }

    public function checkAuth()
    {
        if ($this->getSecurityContext()->hasCustomerUser() === false) {
            $this->redirectToRoute('customer.login.process');
        }
    }

    protected function checkCartNotEmpty()
    {
        $cart = $this->getSession()->getCart();
        if ($cart===null || $cart->countCartItems() == 0) {
            $this->redirectToRoute('cart.view');
        }
    }

    protected function checkValidDelivery()
    {
        $order = $this->getSession()->getOrder();
        if (null === $order
            ||
            null === $order->getChoosenDeliveryAddress()
            ||
            null === $order->getDeliveryModuleId()
            ||
            null === AddressQuery::create()->findPk($order->getChoosenDeliveryAddress())
            ||
            null === ModuleQuery::create()->findPk($order->getDeliveryModuleId())) {
            $this->redirectToRoute("order.delivery");
        }
    }

    protected function checkValidInvoice()
    {
        $order = $this->getSession()->getOrder();
        if (null === $order
            ||
            null === $order->getChoosenInvoiceAddress()
            ||
            null === $order->getPaymentModuleId()
            ||
            null === AddressQuery::create()->findPk($order->getChoosenInvoiceAddress())
            ||
            null === ModuleQuery::create()->findPk($order->getPaymentModuleId())) {
            $this->redirectToRoute("order.invoice");
        }
    }

    /**
     * @return TemplateDefinition the template
     */
    protected function getParser($template = null)
    {
        $parser = $this->container->get("thelia.parser");

        // Define the template that should be used
        $parser->setTemplateDefinition($template ?: TemplateHelper::getInstance()->getActiveFrontTemplate());

        return $parser;
    }

    /**
     * Render the given template, and returns the result as an Http Response.
     *
     * @param  string                               $templateName the complete template name, with extension
     * @param  array                                $args         the template arguments
     * @param  int                                  $status       http code status
     * @return \Thelia\Core\HttpFoundation\Response
     */
    protected function render($templateName, $args = array(), $status = 200)
    {
        return Response::create($this->renderRaw($templateName, $args), $status);
    }

    /**
     * Render the given template, and returns the result as a string.
     *
     * @param $templateName the complete template name, with extension
     * @param array $args        the template arguments
     * @param null  $templateDir
     *
     * @return string
     */
    protected function renderRaw($templateName, $args = array(), $templateDir = null)
    {

        // Add the template standard extension
        $templateName .= '.html';

        $session = $this->getSession();

        // Prepare common template variables
        $args = array_merge($args, array(
                'locale'               => $session->getLang()->getLocale(),
                'lang_code'            => $session->getLang()->getCode(),
                'lang_id'              => $session->getLang()->getId(),
                'current_url'          => $this->getRequest()->getUri()
            ));

        // Render the template.

        $data = $this->getParser($templateDir)->render($templateName, $args);

        return $data;

    }
}
