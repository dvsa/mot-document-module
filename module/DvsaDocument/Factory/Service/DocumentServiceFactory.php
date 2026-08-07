<?php

declare(strict_types=1);

namespace DvsaDocument\Factory\Service;

use DvsaDocument\Service\Document\DocumentService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @psalm-suppress UnusedClass BL-22047
 */
final class DocumentServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param array|null $args
     * @return DocumentService|object
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $name, array $args = null)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $documentService = new DocumentService($entityManager);
        return $documentService;
    }
}
