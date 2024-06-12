<?php

declare(strict_types=1);

namespace DvsaDocument\Factory\Service;

use DvsaDocument\Service\Document\DocumentService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DocumentServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param array|null $args
     * @return DocumentService|object
     */
    public function __invoke(ContainerInterface $container, $name, array $args = null)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $documentService = new DocumentService($entityManager);
        return $documentService;
    }
}
