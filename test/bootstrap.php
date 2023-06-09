<?php

namespace DvsaDocumentModuleTest;

use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;

error_reporting(E_ALL | E_STRICT);
chdir(dirname(__DIR__));

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    protected static $serviceManager;

    public static function init()
    {
        // Setup the autloader
        static::initAutoloader();

        // Grab the application config
        $config = array(
            'modules' => array(
                'DvsaDocument',
                'DvsaReport'
            ),
            'module_listener_options' => array(
                'module_paths' => array(
                    __DIR__ . '/../module'
                )
            )
        );

        $serviceManager = new ServiceManager((new ServiceManagerConfig())->toArray());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        static::$serviceManager = $serviceManager;
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        $loader = require('vendor/autoload.php');

        $loader->addPsr4('DvsaDocumentModuleTest\\', __DIR__);
    }
}

Bootstrap::init();
