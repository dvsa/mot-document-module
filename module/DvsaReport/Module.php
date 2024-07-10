<?php

namespace DvsaReport;

use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;

/**
 * Module Bootstrap
 */
class Module
{
    /**
     * @return (int|string|string[])[][]
     */
    public function getConfig(): array
    {
        return include __DIR__ . '/../../config/report-module.config.php';
    }

    public function onBootstrap(MvcEvent $e): void
    {
        // You may not need to do this if you're doing it elsewhere in your
        // application
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
}
