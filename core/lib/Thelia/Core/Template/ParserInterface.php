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

namespace Thelia\Core\Template;

/**
 *
 * @author Manuel Raynaud <mraynaud@openstudio.fr>
 *
 */

interface ParserInterface
{
    public function render($realTemplateName, array $parameters = array());

    public function setContent($content);

    public function getStatus();

    public function setStatus($status);

    /**
     * Setup the parser with a template definition, which provides a template description.
     *
     * @param TemplateDefinition $templateDefinition
     */
    public function setTemplateDefinition(TemplateDefinition $templateDefinition);

    /**
     * Add a template directory to the current template list
     *
     * @param unknown $templateType the template type (
     *
     * @param string  $templateName      the template name
     * @param string  $templateDirectory path to the template dirtectory
     * @param unknown $key               ???
     * @param string  $unshift           ??? Etienne ?
     */
    public function addTemplateDirectory($templateType, $templateName, $templateDirectory, $key, $unshift = false);

    /**
     * Return the registeted template directories for a givent template type
     *
     * @param  unknown                   $templateType
     * @throws \InvalidArgumentException if the templateType is not defined
     * @return array:                    an array of defined templates directories for the given template type
     */
    public function getTemplateDirectories($templateType);

    /**
     * Create a variable that will be available in the templates
     *
     * @param string $variable the variable name
     * @param mixed  $value    the value of the variable
     */
    public function assign($variable, $value);
}
