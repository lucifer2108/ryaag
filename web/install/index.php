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
?>
<?php
    $step = 1;
    include("header.php");
?>
					<div class="well">
                        <div class="clearfix">
                            <a href="?lang=fr_FR"><span class="glyphicon glyphicon-chevron-right"></span> <?php echo $trans->trans('French'); ?></a>
                            <a href="?lang=en_US"><span class="glyphicon glyphicon-chevron-right"></span> <?php echo $trans->trans('English'); ?></a>
                        </div>

						<p class="lead text-center">
<?php echo $trans->trans('Welcome in the Thelia installation wizard.'); ?>
						</p>
						<p class="text-center">
<?php echo $trans->trans('We will guide you throughout this process to install any application on your system.'); ?>
						</p>
					</div>
					<div class="clearfix">
						<a href="permission.php" class="pull-right btn btn-default btn-primary"><span class="glyphicon glyphicon-chevron-right"></span> <?php echo $trans->trans('Continue'); ?></a>
					</div>
<?php include("footer.php"); ?>