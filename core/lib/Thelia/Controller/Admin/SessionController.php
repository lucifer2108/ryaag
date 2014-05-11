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

namespace Thelia\Controller\Admin;

use Thelia\Form\AdminLogin;
use Thelia\Core\Security\Authentication\AdminUsernamePasswordFormAuthenticator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\Admin;
use Thelia\Model\AdminLog;
use Thelia\Core\Security\Exception\AuthenticationException;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;

use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Security\Authentication\AdminTokenAuthenticator;

use Thelia\Core\Security\Exception\TokenAuthenticationException;

class SessionController extends BaseAdminController
{
    public function showLoginAction()
    {
        // Check if we can authenticate the user with a cookie-based token
        if (null !== $key = $this->getRememberMeKeyFromCookie()) {

            // Create the authenticator
            $authenticator = new AdminTokenAuthenticator($key);

            try {
                // If have found a user, store it in the security context
                $user = $authenticator->getAuthentifiedUser();

                $this->getSecurityContext()->setAdminUser($user);

                $this->adminLogAppend("admin", "LOGIN", "Successful token authentication");

                // Update the cookie
                $this->createAdminRememberMeCookie($user);

                $this->applyUserLocale($user);

                // Render the home page
                return $this->render("home");

            } catch (TokenAuthenticationException $ex) {
                $this->adminLogAppend("admin", "LOGIN", "Token based authentication failed.");

                // Clear the cookie
                $this->clearRememberMeCookie();
            }
        }

        return $this->render("login");
    }

    protected function applyUserLocale(Admin $user)
    {
        // Set the current language according to Admin locale preference
        $locale = $user->getLocale();

        if (null === $lang = LangQuery::create()->findOneByLocale($locale)) {
            $lang = Lang::getDefaultLanguage();
        }

        $this->getSession()->setLang($lang);
    }

    public function checkLogoutAction()
    {
        $this->dispatch(TheliaEvents::ADMIN_LOGOUT);

        $this->getSecurityContext()->clearAdminUser();

        // Clear the remember me cookie, if any
        $this->clearRememberMeCookie();

        // Go back to login page.
        $this->redirectToRoute('admin.login');
    }

    public function checkLoginAction()
    {
        $request = $this->getRequest();

        $adminLoginForm = new AdminLogin($request);

        try {

            $form = $this->validateForm($adminLoginForm, "post");

            $authenticator = new AdminUsernamePasswordFormAuthenticator($request, $adminLoginForm);

            $user = $authenticator->getAuthentifiedUser();

            // Success -> store user in security context
            $this->getSecurityContext()->setAdminUser($user);

            // Log authentication success
            AdminLog::append("admin", "LOGIN", "Authentication successful", $request, $user, false);

            $this->applyUserLocale($user);

            /**
             * we have tou find a way to send cookie
             */
            if (intval($form->get('remember_me')->getData()) > 0) {
                // If a remember me field if present and set in the form, create
                // the cookie thant store "remember me" information
                $this->createAdminRememberMeCookie($user);
            }

            $this->dispatch(TheliaEvents::ADMIN_LOGIN);

            // Redirect to the success URL, passing the cookie if one exists.
            $this->redirect($adminLoginForm->getSuccessUrl());

         } catch (FormValidationException $ex) {

             // Validation problem
             $message = $this->createStandardFormValidationErrorMessage($ex);
         } catch (AuthenticationException $ex) {

             // Log authentication failure
             AdminLog::append("admin", "LOGIN", sprintf("Authentication failure for username '%s'", $authenticator->getUsername()), $request);

             $message =  $this->getTranslator()->trans("Login failed. Please check your username and password.");
         } catch (\Exception $ex) {

             // Log authentication failure
             AdminLog::append("admin", "LOGIN", sprintf("Undefined error: %s", $ex->getMessage()), $request);

             $message = $this->getTranslator()->trans(
                     "Unable to process your request. Please try again (%err).",
                     array("%err" => $ex->getMessage())
             );
         }

         $this->setupFormErrorContext("Login process", $message, $adminLoginForm, $ex);

          // Display the login form again
        return $this->render("login");
    }
}
