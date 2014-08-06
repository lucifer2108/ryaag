<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/
$step=6;
include "header.php";

if($_SESSION['install']['step'] != $step && (empty($_POST['admin_login']) || empty($_POST['admin_password']) || ($_POST['admin_password'] != $_POST['admin_password_verif']))) {
    header('location: config.php?err=1');
}

if($_SESSION['install']['step'] == 5) {
    $admin = new \Thelia\Model\Admin();
    $admin->setLogin($_POST['admin_login'])
        ->setPassword($_POST['admin_password'])
        ->setFirstname('admin')
        ->setLastname('admin')
        ->setLocale(empty($_POST['admin_locale']) ? 'en_US' : $_POST['admin_locale'])
        ->save();


    \Thelia\Model\ConfigQuery::create()
        ->filterByName('store_email')
        ->update(array('Value' => $_POST['store_email']));

    \Thelia\Model\ConfigQuery::create()
        ->filterByName('store_name')
        ->update(array('Value' => $_POST['store_name']));

    \Thelia\Model\ConfigQuery::create()
        ->filterByName('url_site')
        ->update(array('Value' => $_POST['url_site']));
}

//clean up cache directories
$fs = new \Symfony\Component\Filesystem\Filesystem();

$fs->remove(THELIA_ROOT . '/cache/prod');
$fs->remove(THELIA_ROOT . '/cache/dev');


$request = \Thelia\Core\HttpFoundation\Request::createFromGlobals();
$_SESSION['install']['step'] = $step;

// Retrieve the website url
$url = $_SERVER['PHP_SELF'];
$website_url = preg_replace("#/install/[a-z](.*)#" ,'', $url);

?>

    <div class="well">
        <p class="lead text-center">
            <?php echo $trans->trans('Thanks, you have installed Thelia'); ?>
        </p>
        <p class="lead text-center">
            <?php echo $trans->trans('Don\'t forget to delete the web/install directory.'); ?>
        </p>

        <p class="lead text-center">
            <a href="<?php echo $request->getSchemeAndHttpHost().$website_url; ?>/admin"><?php echo $trans->trans('Go to back office'); ?></a>
        </p>

    </div>
<?php include "footer.php"; ?>
