<?php

namespace DvsaReport\Service\Pdf;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class PdfRendererFactory
 *
 */
class PdfRendererFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     *
     * @return PdfRenderer
     */
    public function __invoke(ContainerInterface $container, mixed $name, array $args = null)
    {
        $renderer = new PdfRenderer();
        return $renderer;
    }
}
