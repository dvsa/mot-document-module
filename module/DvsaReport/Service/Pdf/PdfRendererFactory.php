<?php

namespace DvsaReport\Service\Pdf;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class PdfRendererFactory
 * @psalm-suppress UnusedClass BL-22047
 */
final class PdfRendererFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param mixed $name
     * @param array|null $args
     * @return PdfRenderer
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, mixed $name, array $args = null)
    {
        $renderer = new PdfRenderer();
        return $renderer;
    }
}
