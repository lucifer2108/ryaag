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

namespace Thelia\Core\Template\Smarty;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\EventDispatcher\EventDispatcherInterface;

use \Smarty;

use Thelia\Core\Template\ParserInterface;

use Thelia\Core\Template\Exception\ResourceNotFoundException;
use Thelia\Core\Template\ParserContext;
use Thelia\Core\Template\TemplateDefinition;
use Imagine\Exception\InvalidArgumentException;
use Thelia\Core\Translation\Translator;
use Thelia\Model\ConfigQuery;

/**
 *
 * @author Franck Allimant <franck@cqfdev.fr>
 * @author Etienne Roudeix <eroudeix@openstudio.fr>
 */
class SmartyParser extends Smarty implements ParserInterface
{
    public $plugins = array();

    protected $request;
    protected $dispatcher;
    protected $parserContext;

    protected $backOfficeTemplateDirectories = array();
    protected $frontOfficeTemplateDirectories = array();

    protected $templateDirectories = array();

    /**
     * @var TemplateDefinition
     */
    protected $templateDefinition = "";

    protected $status = 200;

    /**
     * @param Request                  $request
     * @param EventDispatcherInterface $dispatcher
     * @param ParserContext            $parserContext
     * @param string                   $env
     * @param bool                     $debug
     */
    public function __construct(
        Request $request, EventDispatcherInterface $dispatcher, ParserContext $parserContext,
        $env = "prod", $debug = false)
    {
        parent::__construct();

        $this->request = $request;
        $this->dispatcher = $dispatcher;
        $this->parserContext = $parserContext;

        // Configure basic Smarty parameters

        $compile_dir = THELIA_ROOT . 'cache/'. $env .'/smarty/compile';
        if (! is_dir($compile_dir)) @mkdir($compile_dir, 0777, true);

        $cache_dir = THELIA_ROOT . 'cache/'. $env .'/smarty/cache';
        if (! is_dir($cache_dir)) @mkdir($cache_dir, 0777, true);

        $this->setCompileDir($compile_dir);
        $this->setCacheDir($cache_dir);

        $this->debugging = $debug;

        // Prevent smarty ErrorException: Notice: Undefined index bla bla bla...
        $this->error_reporting = E_ALL ^ E_NOTICE;

        // Si on n'est pas en mode debug, activer le cache, avec une lifetime de 15mn, et en vérifiant que les templates sources n'ont pas été modifiés.

        if ($debug) {
            $this->setCaching(Smarty::CACHING_OFF);
            $this->setForceCompile(true);
        } else {
            $this->setForceCompile(false);
        }

        // The default HTTP status
        $this->status = 200;

        $this->registerFilter('output', array($this, "trimWhitespaces"));
        $this->registerFilter('variable', array(__CLASS__, "theliaEscape"));
    }

    /**
     * Trim whitespaces from the HTML output, preserving required ones in pre, textarea, javascript.
     * This methois uses 3 levels of trimming :
     *
     *    - 0 : whitespaces are not trimmed, code remains as is.
     *    - 1 : only blank lines are trimmed, code remains indented and human-readable (the default)
     *    - 2 or more : all unnecessary whitespace are removed. Code is very hard to read.
     *
     * The trim level is defined by the configuration variable html_output_trim_level
     *
     * @param  string                    $source   the HTML source
     * @param  \Smarty_Internal_Template $template
     * @return string
     */
    public function trimWhitespaces($source, \Smarty_Internal_Template $template)
    {
        $compressionMode = ConfigQuery::read('html_output_trim_level', 1);

        if ($compressionMode == 0) {
            return $source;
        }

        $store = array();
        $_store = 0;
        $_offset = 0;

        // Unify Line-Breaks to \n
        $source = preg_replace("/\015\012|\015|\012/", "\n", $source);

        // capture Internet Explorer Conditional Comments
        if ($compressionMode == 1) {
            $expressions = array(
                // remove spaces between attributes (but not in attribute values!)
                '#(([a-z0-9]\s*=\s*(["\'])[^\3]*?\3)|<[a-z0-9_]+)\s+([a-z/>])#is' => '\1 \4',
                '/(^[\n]*|[\n]+)[\s\t]*[\n]+/' => "\n"
            );
        } elseif ($compressionMode >= 2) {
            if (preg_match_all('#<!--\[[^\]]+\]>.*?<!\[[^\]]+\]-->#is', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $store[] = $match[0][0];
                    $_length = strlen($match[0][0]);
                    $replace = '@!@SMARTY:' . $_store . ':SMARTY@!@';
                    $source = substr_replace($source, $replace, $match[0][1] - $_offset, $_length);

                    $_offset += $_length - strlen($replace);
                    $_store++;
                }
            }

            // Strip all HTML-Comments
            // yes, even the ones in <script> - see http://stackoverflow.com/a/808850/515124
            $source = preg_replace( '#<!--.*?-->#ms', '', $source );

            $expressions = array(
                // replace multiple spaces between tags by a single space
                // can't remove them entirely, becaue that might break poorly implemented CSS display:inline-block elements
                '#(:SMARTY@!@|>)\s+(?=@!@SMARTY:|<)#s' => '\1 \2',
                // remove spaces between attributes (but not in attribute values!)
                '#(([a-z0-9]\s*=\s*(["\'])[^\3]*?\3)|<[a-z0-9_]+)\s+([a-z/>])#is' => '\1 \4',
                // note: for some very weird reason trim() seems to remove spaces inside attributes.
                // maybe a \0 byte or something is interfering?
                '#^\s+<#Ss' => '<',
                '#>\s+$#Ss' => '>',

            );
        } else {
            $expressions = array();
        }

        // capture html elements not to be messed with
        $_offset = 0;
        if (preg_match_all('#<(script|pre|textarea)[^>]*>.*?</\\1>#is', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $store[] = $match[0][0];
                $_length = strlen($match[0][0]);
                $replace = '@!@SMARTY:' . $_store . ':SMARTY@!@';
                $source = substr_replace($source, $replace, $match[0][1] - $_offset, $_length);

                $_offset += $_length - strlen($replace);
                $_store++;
            }
        }

        $source = preg_replace( array_keys($expressions), array_values($expressions), $source );
        // note: for some very weird reason trim() seems to remove spaces inside attributes.
        // maybe a \0 byte or something is interfering?
        // $source = trim( $source );

        // capture html elements not to be messed with
        $_offset = 0;
        if (preg_match_all('#@!@SMARTY:([0-9]+):SMARTY@!@#is', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $store[] = $match[0][0];
                $_length = strlen($match[0][0]);
                $replace = array_shift($store);
                $source = substr_replace($source, $replace, $match[0][1] + $_offset, $_length);

                $_offset += strlen($replace) - $_length;
                $_store++;
            }
        }

        return $source;
    }

    /**
     * Add a template directory to the current template list
     *
     * @param int     $templateType      the template type (a TemplateDefinition type constant)
     * @param string  $templateName      the template name
     * @param string  $templateDirectory path to the template dirtectory
     * @param string  $key               ???
     * @param boolean $unshift           ??? Etienne ?
     */
    public function addTemplateDirectory($templateType, $templateName, $templateDirectory, $key, $unshift = false)
    {
        if (true === $unshift && isset($this->templateDirectories[$templateType][$templateName])) {

            $this->templateDirectories[$templateType][$templateName] = array_merge(
                array(
                    $key => $templateDirectory,
                ),
                $this->templateDirectories[$templateType][$templateName]
            );
        } else {
            $this->templateDirectories[$templateType][$templateName][$key] = $templateDirectory;
        }
    }

    /**
     * Return the registeted template directories for a givent template type
     *
     * @param  int                      $templateType
     * @throws InvalidArgumentException
     * @return mixed:
     */
    public function getTemplateDirectories($templateType)
    {
        if (! isset($this->templateDirectories[$templateType])) {
            throw new InvalidArgumentException("Failed to get template type %", $templateType);
        }

        return $this->templateDirectories[$templateType];
    }

    public static function theliaEscape($content, $smarty)
    {
        if (is_scalar($content)) {
            return htmlspecialchars($content, ENT_QUOTES, Smarty::$_CHARSET);
        } else {
            return $content;
        }
    }

    /**
     * @param TemplateDefinition $templateDefinition
     */
    public function setTemplateDefinition(TemplateDefinition $templateDefinition)
    {
        $this->templateDefinition = $templateDefinition;

        /* init template directories */
        $this->setTemplateDir(array());

        /* define config directory */
        $configDirectory = THELIA_TEMPLATE_DIR . $this->getTemplate() . '/configs';
        $this->addConfigDir($configDirectory, 0);

        /* add modules template directories */
        $this->addTemplateDirectory(
            $templateDefinition->getType(),
            $templateDefinition->getName(),
            THELIA_TEMPLATE_DIR . $this->getTemplate(),
            '0',
            true
        );

        /* do not pass array directly to addTemplateDir since we cant control on keys */
        if (isset($this->templateDirectories[$templateDefinition->getType()][$templateDefinition->getName()])) {
            foreach ($this->templateDirectories[$templateDefinition->getType()][$templateDefinition->getName()] as $key => $directory) {
                $this->addTemplateDir($directory, $key);
                $this->addConfigDir($directory . "/configs", $key);
            }
        }
    }

    /**
     * Get template definition
     *
     * @param bool $webAssetTemplate Allow to load asset from another template
     *                               If the name of the template if provided
     *
     * @return TemplateDefinition
     */
    public function getTemplateDefinition($webAssetTemplate = false)
    {
        $ret = $this->templateDefinition;
        if (false !== $webAssetTemplate) {
            $customPath = str_replace($ret->getName(), $webAssetTemplate, $ret->getPath());
            $ret->setName($webAssetTemplate);
            $ret->setPath($customPath);

        }

        return $ret;
    }

    public function getTemplate()
    {
        return $this->templateDefinition->getPath();
    }
    /**
     * Return a rendered template, either from file or ftom a string
     *
     * @param string $resourceType    either 'string' (rendering from a string) or 'file' (rendering a file)
     * @param string $resourceContent the resource content (a text, or a template file name)
     * @param array  $parameters      an associative array of names / value pairs
     *
     * @return string the rendered template text
     */
    protected function internalRenderer($resourceType, $resourceContent, array $parameters)
    {
        // Assign the parserContext variables
        foreach ($this->parserContext as $var => $value) {
            $this->assign($var, $value);
        }

        $this->assign($parameters);

        return $this->fetch(sprintf("%s:%s", $resourceType, $resourceContent));
    }

    /**
     * Return a rendered template file
     *
     * @param  string $realTemplateName the template name (from the template directory)
     * @param  array  $parameters       an associative array of names / value pairs
     * @return string the rendered template text
     */
    public function render($realTemplateName, array $parameters = array())
    {
        if (false === $this->templateExists($realTemplateName) || false === $this->checkTemplate($realTemplateName)) {
            throw new ResourceNotFoundException(Translator::getInstance()->trans("Template file %file cannot be found.", array('%file' => $realTemplateName)));
        }

        return $this->internalRenderer('file', $realTemplateName, $parameters);

    }

    private function checkTemplate($fileName)
    {
        $templates = $this->getTemplateDir();

        $found = true;
        foreach ($templates as $key => $value) {
            $absolutePath = rtrim(realpath(dirname($value.$fileName)), "/");
            $templateDir =  rtrim(realpath($value), "/");
            if (!empty($absolutePath) && strpos($absolutePath, $templateDir) !== 0) {
                $found = false;
            }
        }

       return $found;
    }

    /**
     * Return a rendered template text
     *
     * @param  string $templateText the template text
     * @param  array  $parameters   an associative array of names / value pairs
     * @return string the rendered template text
     */
    public function renderString($templateText, array $parameters = array())
    {
        return $this->internalRenderer('string', $templateText, $parameters);
    }

    /**
     *
     * set $content with the body of the response or the Response object directly
     *
     * @param string|Thelia\Core\HttpFoundation\Response $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     *
     * @return type the status of the response
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * status HTTP of the response
     *
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function addPlugins(AbstractSmartyPlugin $plugin)
    {
        $this->plugins[] = $plugin;
    }

    public function registerPlugins()
    {
        foreach ($this->plugins as $register_plugin) {
            $plugins = $register_plugin->getPluginDescriptors();

            if (!is_array($plugins)) {
                $plugins = array($plugins);
            }

            foreach ($plugins as $plugin) {
                $this->registerPlugin(
                    $plugin->getType(),
                    $plugin->getName(),
                    array(
                        $plugin->getClass(),
                        $plugin->getMethod()
                    )
                );
            }
        }
    }

}
