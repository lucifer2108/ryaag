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

namespace Thelia\Core\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Template\Exception\ResourceNotFoundException;
use Thelia\Exception\OrderException;

/**
 *
 * ViewSubscriber Main class subscribing to view http response.
 *
 * @TODO Look if it's possible to block this definition in dependency-injection
 *
 * @author Manuel Raynaud <manu@thelia.net>
 */

class ViewListener implements EventSubscriberInterface
{
    /**
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterfac $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     *
     * Launch the parser defined on the constructor and get the result.
     *
     * The result is transform id needed into a Response object
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $parser = $this->container->get('thelia.parser');
        $templateHelper = $this->container->get('thelia.template_helper');
        $parser->setTemplateDefinition($templateHelper->getActiveFrontTemplate());
        $request = $this->container->get('request');
        $response = null;
        try {
            $view = $request->attributes->get('_view').".html";

            $content = $parser->render($view);

            // Save the current template information
            $request->getSession()->saveCurrentTemplate($view, []);

            if ($content instanceof Response) {
                $response = $content;
            } else {
                $response = new Response($content, $parser->getStatus() ?: 200);
            }
        } catch (ResourceNotFoundException $e) {
            throw new NotFoundHttpException();
        } catch (OrderException $e) {
            switch ($e->getCode()) {
                case OrderException::CART_EMPTY:
                    // Redirect to the cart template
                    $response = RedirectResponse::create($this->container->get('router.chainRequest')->generate($e->cartRoute, $e->arguments, Router::ABSOLUTE_URL));
                    break;
                case OrderException::UNDEFINED_DELIVERY:
                    // Redirect to the delivery choice template
                    $response = RedirectResponse::create($this->container->get('router.chainRequest')->generate($e->orderDeliveryRoute, $e->arguments, Router::ABSOLUTE_URL));
                    break;
            }
            if (null === $response) {
                throw $e;
            }
        }

        $event->setResponse($response);
    }

    public function beforeKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $this->container->get('request');

        if (null === $request->attributes->get('_view')) {
            $request->attributes->set('_view', $this->findView($request));
        }
    }

    public function findView(Request $request)
    {
        if (! $view = $request->query->get('view')) {
            $view = "index";
            if ($request->request->has('view')) {
                $view = $request->request->get('view');
            }
        }

        return $view;
    }


    /**
     *
     * Register the method to execute in this class for a specific event (here the kernel.view event)
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW =>array(
                array('onKernelView', 0),
                array('beforeKernelView', 5)
            )
        );
    }
}
