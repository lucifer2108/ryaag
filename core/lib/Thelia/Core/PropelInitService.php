<?php

namespace Thelia\Core;

use Propel\Generator\Command\ConfigConvertCommand;
use Propel\Generator\Command\ModelBuildCommand;
use Propel\Runtime\Connection\ConnectionWrapper;
use Propel\Runtime\Propel;
use Symfony\Component\ClassLoader\ClassLoader;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Thelia\Config\DatabaseConfigurationSource;
use Thelia\Core\Propel\Schema\SchemaCombiner;
use Thelia\Core\Propel\Schema\SchemaLocator;
use Thelia\Log\Tlog;

/**
 * Propel cache and initialization service.
 */
class PropelInitService
{
    /**
     * Name of the Propel initialization file.
     * @var string
     */
    protected static $PROPEL_CONFIG_CACHE_FILENAME = 'propel.init.php';

    /**
     * Application environment.
     * @var string
     */
    protected $environment;

    /**
     * Whether the application is in debug mode.
     * @var bool
     */
    protected $debug;

    /**
     * Map of environment parameters.
     * @var array
     */
    protected $envParameters = [];

    /**
     * Propel schema locator service.
     * @var SchemaLocator
     */
    protected $schemaLocator;

    /**
     * @param string $environment Application environment.
     * @param bool $debug Whether the application is in debug mode.
     * @param array $envParameters Map of environment parameters.
     * @param SchemaLocator $schemaLocator Propel schema locator service.
     */
    public function __construct(
        $environment,
        $debug,
        array $envParameters,
        SchemaLocator $schemaLocator
    ) {
        $this->environment = $environment;
        $this->debug = $debug;
        $this->envParameters = $envParameters;
        $this->schemaLocator = $schemaLocator;
    }

    /**
     * @return string Thelia database configuration file.
     */
    protected function getTheliaDatabaseConfigFile()
    {
        $fs = new Filesystem();

        $databaseConfigFile = THELIA_CONF_DIR . 'database_' . $this->environment . '.yml';
        if (!$fs->exists($databaseConfigFile)) {
            $databaseConfigFile = THELIA_CONF_DIR . 'database.yml';
        }

        return $databaseConfigFile;
    }

    /**
     * @return string Propel subdirectory in the Thelia cache directory.
     */
    public function getPropelCacheDir()
    {
        return THELIA_CACHE_DIR . $this->environment . DS . 'propel' . DS;
    }

    /**
     * @return string Propel configuration directory.
     */
    public function getPropelConfigDir()
    {
        return $this->getPropelCacheDir() . 'config' . DS;
    }

    /**
     * @return string Propel cached configuration file.
     */
    public function getPropelConfigFile()
    {
        return $this->getPropelConfigDir() . 'propel.yml';
    }

    /**
     * @return string Propel cached initialization file.
     */
    public function getPropelInitFile()
    {
        return $this->getPropelConfigDir() . static::$PROPEL_CONFIG_CACHE_FILENAME;
    }

    /**
     * @return string Generated global Propel schema(s) directory.
     */
    public function getPropelSchemaDir()
    {
        return $this->getPropelCacheDir() . 'schema' . DS;
    }

    /**
     * @return string Generated Propel models directory.
     */
    public function getPropelModelDir()
    {
        return $this->getPropelCacheDir() . 'model' . DS;
    }

    /**
     * @return string Generated Propel migrations directory.
     */
    public function getPropelMigrationDir()
    {
        return $this->getPropelCacheDir() . 'migration' . DS;
    }

    /**
     * Run a Propel command.
     * @param Command $command Command to run.
     * @param array $parameters Command parameters.
     * @param OutputInterface|null $output Command output.
     * @return int Command exit code.
     * @throws \Exception
     */
    public function runCommand(Command $command, array $parameters = [], OutputInterface $output = null)
    {
        $parameters['command'] = $command->getName();
        $input = new ArrayInput($parameters);

        if ($output === null) {
            $output = new NullOutput();
        }

        $command->setApplication(new SymfonyConsoleApplication());

        return $command->run($input, $output);
    }

    /**
     * Generate the Propel configuration file.
     */
    public function buildPropelConfig()
    {
        $propelConfigCache = new ConfigCache(
            $this->getPropelConfigFile(),
            $this->debug
        );

        if ($propelConfigCache->isFresh()) {
            return;
        }

        $configService = new DatabaseConfigurationSource(
            Yaml::parse(file_get_contents($this->getTheliaDatabaseConfigFile())),
            $this->envParameters
        );

        $propelConfig = $configService->getPropelConnectionsConfiguration();

        $propelConfig['propel']['paths']['phpDir'] = THELIA_ROOT;
        $propelConfig['propel']['generator']['objectModel']['builders'] = [
            'object'
            => '\Thelia\Core\Propel\Generator\Builder\Om\ObjectBuilder',
            'objectstub'
            => '\Thelia\Core\Propel\Generator\Builder\Om\ExtensionObjectBuilder',
            'objectmultiextend'
            => '\Thelia\Core\Propel\Generator\Builder\Om\MultiExtendObjectBuilder',
            'query'
            => '\Thelia\Core\Propel\Generator\Builder\Om\QueryBuilder',
            'querystub'
            => '\Thelia\Core\Propel\Generator\Builder\Om\ExtensionQueryBuilder',
            'queryinheritance'
            => '\Thelia\Core\Propel\Generator\Builder\Om\QueryInheritanceBuilder',
            'queryinheritancestub'
            => '\Thelia\Core\Propel\Generator\Builder\Om\ExtensionQueryInheritanceBuilder',
            'tablemap'
            => '\Thelia\Core\Propel\Generator\Builder\Om\TableMapBuilder',
        ];

        $propelConfigCache->write(
            Yaml::dump($propelConfig),
            [new FileResource($this->getTheliaDatabaseConfigFile())]
        );
    }

    /**
     * Generate the Propel initialization file.
     * @throws \Exception
     */
    public function buildPropelInitFile()
    {
        $propelInitCache = new ConfigCache(
            $this->getPropelInitFile(),
            $this->debug
        );

        if ($propelInitCache->isFresh()) {
            return;
        }

        $this->runCommand(
            new ConfigConvertCommand(),
            [
                '--config-dir' => $this->getPropelConfigDir(),
                '--output-dir' => $this->getPropelConfigDir(),
                '--output-file' => static::$PROPEL_CONFIG_CACHE_FILENAME,
            ]
        );

        // rewrite the file as a cached file
        $propelInitContent = file_get_contents($this->getPropelInitFile());
        $propelInitCache->write(
            $propelInitContent,
            [new FileResource($this->getPropelConfigFile())]
        );
    }

    /**
     * Generate the global Propel schema(s).
     */
    public function buildPropelGlobalSchema()
    {
        $fs = new Filesystem();

        // TODO: caching rules ?
        if ($fs->exists($this->getPropelSchemaDir())) {
            return;
        }

        $fs->mkdir($this->getPropelSchemaDir());

        $schemaCombiner = new SchemaCombiner(
            $this->schemaLocator->findForActiveModules()
        );

        foreach ($schemaCombiner->getDatabases() as $database) {
            $databaseSchemaCache = new ConfigCache(
                "{$this->getPropelSchemaDir()}{$database}.schema.xml",
                $this->debug
            );

            $databaseSchemaResources = [];
            foreach ($schemaCombiner->getSourceDocuments($database) as $sourceDocument) {
                $databaseSchemaResources[] = new FileResource($sourceDocument->baseURI);
            }

            $databaseSchemaCache->write(
                $schemaCombiner->getCombinedDocument($database)->saveXML(),
                $databaseSchemaResources
            );
        }
    }

    /**
     * Generate the base Propel models.
     * @throws \Exception
     */
    public function buildPropelModels()
    {
        $fs = new Filesystem();

        // TODO: caching rules ?
        if ($fs->exists($this->getPropelModelDir())) {
            return;
        }

        $this->runCommand(
            new ModelBuildCommand(),
            [
                '--config-dir' => $this->getPropelConfigDir(),
                '--schema-dir' => $this->getPropelSchemaDir(),
            ]
        );
    }

    /**
     * Register a class loader to load the generated Propel models.
     */
    public function registerPropelModelLoader()
    {
        $loader = new ClassLoader();

        $loader->addPrefix(
            '', // no prefix, models already define their full namespace
            $this->getPropelModelDir()
        );

        $loader->register(
            true // prepend the autoloader to use cached models first
        );
    }

    /**
     * Initialize the Propel environment and connection.
     * @return bool Whether a Propel connection is available.
     * @throws \Exception
     */
    public function init()
    {
        if (!file_exists($this->getTheliaDatabaseConfigFile())) {
            return false;
        }

        // this will be used in our Propel model builders
        if (!defined('THELIA_PROPEL_BUILDER_ENVIRONMENT')) {
            define('THELIA_PROPEL_BUILDER_ENVIRONMENT', $this->environment);
        }

        $this->buildPropelConfig();

        $this->buildPropelInitFile();
        require $this->getPropelInitFile();

        $theliaDatabaseConnection = Propel::getConnection('thelia');
        $this->schemaLocator->setTheliaDatabaseConnection($theliaDatabaseConnection);

        $this->buildPropelGlobalSchema();

        $this->buildPropelModels();
        $this->registerPropelModelLoader();

        $theliaDatabaseConnection->setAttribute(ConnectionWrapper::PROPEL_ATTR_CACHE_PREPARES, true);

        if ($this->debug) {
            // In debug mode, we have to initialize Tlog at this point, as this class uses Propel
            Tlog::getInstance()->setLevel(Tlog::DEBUG);

            Propel::getServiceContainer()->setLogger('defaultLogger', Tlog::getInstance());
            $theliaDatabaseConnection->useDebug(true);
        }

        return true;
    }
}
