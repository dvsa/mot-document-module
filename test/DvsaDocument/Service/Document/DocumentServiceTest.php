<?php

/**
 * DocumentService Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace DvsaDocumentModuleTest\DvsaDocument\Service\Document;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use DvsaDocument\Service\Document\DocumentService;
use DvsaDocument\Exceptions\TemplateNotFoundException;
use DvsaDocument\Exceptions\EmptyDocumentException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use DvsaDocument\Entity\Document;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * DocumentService Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class DocumentServiceTest extends TestCase
{
    protected DocumentService $service;

    protected MockObject&EntityManager $em;

    protected ServiceManager $sm;

    #[\Override]
    public function setUp(): void
    {
        $entityManager = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->service = new DocumentService($entityManager);
    }

    /**
     * @return void
     */
    public function testGetReportNameWithInvalidIdThrowsExpectedException(): void
    {
        $query = $this->getMockBuilder(\stdClass::class)->disableOriginalConstructor()->addMethods(['getSingleResult'])->getMock();
        $query->expects($this->once())
            ->method('getSingleResult')
            ->will($this->throwException(new NoResultException()));

        $qb = $this->getQueryBuilderMock(
            ['select', 'from', 'innerJoin', 'where'],
            ['setParameters', 'getQuery']
        );

        $qb->expects($this->once())
            ->method('setParameters')
            ->with($this->equalTo([1]));

        $qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->mockEntityServiceWithQueryBuilder($qb);

        try {
            $this->service->getReportName(1);
        } catch (TemplateNotFoundException $e) {
            $this->assertEquals('Template not found', $e->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    /**
     * @return void
     */
    public function testGetReportNameWithVariationSetsCorrectParameters(): void
    {
        /*
         * we're not really interested in the full result here; we just
         * want to assert that doctrine is called with the correct params.
         * Unfortunately due to the small public interface we have to exercise
         * the whole method
         */
        $query = $this->getMockBuilder(\stdClass::class)->disableOriginalConstructor()->addMethods(['getSingleResult'])->getMock();
        $query->expects($this->once())
            ->method('getSingleResult')
            ->will($this->throwException(new NoResultException()));

        $qb = $this->getQueryBuilderMock(
            ['select', 'from', 'where'],
            ['setParameters', 'getQuery', 'innerJoin']
        );

        $qb->expects($this->exactly(2))
            ->method('innerJoin')
            ->will($this->returnSelf());

        $qb->expects($this->once())
            ->method('setParameters')
            ->with($this->equalTo([1, 'W']));

        $qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->mockEntityServiceWithQueryBuilder($qb);

        try {
            $this->service->getReportName(1, 'W');
        } catch (TemplateNotFoundException $e) {
            $this->assertEquals('Template not found', $e->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    /**
     * @return void
     * @throws TemplateNotFoundException
     */
    public function testGetReportNameWhenSuccessful(): void
    {
        $query = $this->getMockBuilder(\stdClass::class)->disableOriginalConstructor()->addMethods(['getSingleResult'])->getMock();
        $query->expects($this->once())
            ->method('getSingleResult')
            ->will($this->returnValue(['jasperReportName' => 'a-test-report']));

        $qb = $this->getQueryBuilderMock(
            ['select', 'from', 'innerJoin', 'where'],
            ['setParameters', 'getQuery']
        );

        $qb->expects($this->once())
            ->method('setParameters')
            ->with($this->equalTo([1]));

        $qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->mockEntityServiceWithQueryBuilder($qb);

        $this->assertEquals('a-test-report', $this->service->getReportName(1));
    }

    /**
     * @return void
     * @throws EmptyDocumentException
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws OptimisticLockException
     */
    public function testCreateSnapshotWithInvalidTemplateThrowsExpectedException(): void
    {
        $query = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->addMethods(['getSingleResult'])->getMock();
        $query->expects($this->once())
            ->method('getSingleResult')
            ->will($this->throwException(new NoResultException()));

        $qb = $this->getQueryBuilderMock(
            ['select', 'from', 'innerJoin', 'where'],
            ['setParameters', 'getQuery']
        );

        $qb->expects($this->once())
            ->method('setParameters')
            ->with($this->equalTo(['name' => 'a-template', 'isActive' => true]))
            ->will($this->returnSelf());

        $qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->mockEntityServiceWithQueryBuilder($qb);

        try {
            /**
             * This test intentionally passes an argument of the wrong type
             * to the function and checks that it throws an exception
             *
             * @psalm-suppress InvalidArgument
             * @phpstan-ignore-next-line
             */
            $this->service->createSnapshot('a-template', ['foo' => 'bar'], 1);
        } catch (TemplateNotFoundException $e) {
            $this->assertEquals('Template \'a-template\' not found', $e->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    /**
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws OptimisticLockException
     * @throws TemplateNotFoundException
     */
    public function testCreateSnapshotWithEmptyDocumentThrowsExpectedException(): void
    {
        try {
            $this->service->createSnapshot('a-template', 1, []);
        } catch (EmptyDocumentException $e) {
            $this->assertEquals('An empty document cannot be created', $e->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    /**
     * @return void
     * @throws EmptyDocumentException
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws OptimisticLockException
     * @throws TemplateNotFoundException
     */
    public function testCreateSnapshotWithValidDataReturnsIdentifier(): void
    {
        $query = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->addMethods(['getSingleResult'])->getMock();
        $query->expects($this->once())
            ->method('getSingleResult')
            ->will($this->returnValue(['id' => 1234]));

        $qb = $this->getQueryBuilderMock(
            ['select', 'from', 'innerJoin', 'where'],
            ['setParameters', 'getQuery']
        );

        $qb->expects($this->once())
            ->method('setParameters')
            ->with($this->equalTo(['name' => 'a-template', 'isActive' => true]))
            ->will($this->returnSelf());

        $qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $methods = ['createQueryBuilder', 'flush', 'persist'];
        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->onlyMethods($methods)->getMock();
        $em->expects($this->any())
            ->method('persist')
            ->will($this->returnCallback([$this, 'mockPersist']));

        $em->expects($this->once())
            ->method('flush');

        $em->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($qb));

        $this->mockEntityServiceWithQueryBuilder($qb, $methods, $em);

        $data = [
            'foo' => 'bar',
            'baz' => 'test',
            'repeatable' => ['1', '2']
        ];

        /**
         * This test intentionally passes an argument of the wrong type
         * to the function and checks that it throws an exception
         *
         * @psalm-suppress InvalidArgument
         * @phpstan-ignore-next-line
         */
        $actualDocumentId =  $this->service->createSnapshot('a-template', $data, 1);

        $this->assertEquals(4321, $actualDocumentId);
    }

    /**
     * @param array $selfMethods
     * @param array $extraMethods
     *
     * @return MockObject
     */
    protected function getQueryBuilderMock($selfMethods, $extraMethods): MockObject
    {
        $qb = $this->getMockBuilder(\stdClass::class)->disableOriginalConstructor()->addMethods(array_merge($selfMethods, $extraMethods))->getMock();
        foreach ($selfMethods as $method) {
            $qb->expects($this->once())
                ->method($method)
                ->will($this->returnSelf());
        }

        return $qb;
    }

    /**
     * @param mixed $qb
     * @param array $methods
     * @param EntityManager|null $em
     */
    protected function mockEntityServiceWithQueryBuilder($qb, $methods = ['createQueryBuilder'], $em = null): void
    {
        if (!empty($em)) {
            $this->service = new DocumentService($em);
            return;
        }

        $this->em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->onlyMethods($methods)->getMock();

        $this->em->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($qb));

        $this->service = new DocumentService($this->em);
    }

    /**
     * @param array $config
     */
    protected function setConfig($config): void
    {
        $this->sm->setService('Config', $config);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    protected function mockFieldValue($key, $value): array
    {
        $field = $this->getMockBuilder(\stdClass::class)->disableOriginalConstructor()->addMethods(['getFieldValue'])->getMock();
        $field->expects($this->once())
            ->method('getFieldValue')
            ->will($this->returnValue($value));

        return [$key, $field];
    }

    /**
     * @param mixed $entity
     * @throws \Exception
     */
    public function mockPersist($entity): void
    {
        if ($entity instanceof Document) {
            $entity->setId(4321);
        }
    }

    /**
     * Test delete snapshot service with no document found
     * @group current
     */
    public function testDeleteSnapshotWithEmptyDocument(): void
    {
        $this->expectException(EmptyDocumentException::class);
            $entityManager = $this->getMockBuilder(EntityManager::class)
                ->disableOriginalConstructor()->onlyMethods(array('find', 'remove', 'flush'))->getMock();
            $documentService = new DocumentService($entityManager);
            $documentService->deleteSnapshot(-1);
    }

    /**
     * Test delete snapshot service
     * @group current
     */
    public function testDeleteSnapshot(): void
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->onlyMethods(array('find', 'remove', 'flush'))->getMock();

        $entityManager->expects($this->once())
                ->method('find')
                ->with($this->equalTo('DvsaDocument\Entity\Document'), 1)
                ->will($this->returnValue(new \DvsaDocument\Entity\Document()));

        $entityManager->expects($this->once())->method('remove');
        $entityManager->expects($this->once())->method('flush');

        $documentService = new DocumentService($entityManager);

        $documentService->deleteSnapshot(1);
    }
}
