<?php

namespace DvsaReport;

use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;

/**
 * Module Bootstrap
 */
final class Module
{
    /**
     * @return array<array<array<string>|int|string>>
     */
    public function getConfig(): array
    {
        /** @var array<array<array<string>|int|string>> $config */
        $config = include __DIR__ . '/../../config/report-module.config.php';
        return $config;
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
