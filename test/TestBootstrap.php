<?php

namespace DvsaDocumentModuleTest;

use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Test bootstrap, for setting up autoloading
 */
final class TestBootstrap
{
    protected static ServiceManager $serviceManager;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function init(): void
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

        $serviceManagerConfig = (new ServiceManagerConfig())->toArray();
        /** @phpstan-ignore-next-line */
        $serviceManager = new ServiceManager($serviceManagerConfig);
        $serviceManager->setService('ApplicationConfig', $config);
        /** @var ModuleManager $moduleManager */
        $moduleManager = $serviceManager->get('ModuleManager');
        $moduleManager->loadModules();
        static::$serviceManager = $serviceManager;
    }

    /**
     * @return ServiceManager
     */
    public static function getServiceManager(): ServiceManager
    {
        return static::$serviceManager;
    }
}
