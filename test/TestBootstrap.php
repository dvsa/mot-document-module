<?php

namespace DvsaDocumentModuleTest;

use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;

/**
 * Test bootstrap, for setting up autoloading
 */
class TestBootstrap
{
    /** @var ServiceManager */
    protected static $serviceManager;

    /**
     * @return void
     */
    public static function init()
    {
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

        /** @psalm-suppress ArgumentTypeCoercion */
        $serviceManager = new ServiceManager((new ServiceManagerConfig())->toArray());
        $serviceManager->setService('ApplicationConfig', $config);
        /** @var \Laminas\ModuleManager\ModuleManager */
        $moduleManager = $serviceManager->get('ModuleManager');
        $moduleManager->loadModules();
        static::$serviceManager = $serviceManager;
    }

    /**
     * @return ServiceManager
     */
    public static function getServiceManager()
    {
        return static::$serviceManager;
    }
}
