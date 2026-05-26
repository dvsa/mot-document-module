<?php

/**
 * Report Name Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocumentModuleTest\DvsaDocument\Controller;

use DvsaDocument\Service\Document\DocumentService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use DvsaDocumentModuleTest\TestBootstrap as Bootstrap;
use DvsaDocument\Controller\ReportNameController;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Router\RouteMatch;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\TreeRouteStack as HttpRouter;
use DvsaDocument\Exceptions\TemplateNotFoundException;
use Laminas\View\Model\JsonModel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Report Name Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ReportNameControllerTest extends TestCase
{
    /**
     * @param MockObject&DocumentService $documentServiceMock
     * @param int|null $id
     * @param mixed $variation
     *
     * @return ReportNameController
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function setUpController(DocumentService&MockObject $documentServiceMock, ?int $id, mixed $variation): ReportNameController
    {
        $controller = new ReportNameController($documentServiceMock);

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        $request = new Request();
        $response = new Response();
        $routeMatch = new RouteMatch(
            array(
                'id' => $id,
                'variation' => $variation
            )
        );

        $event = new MvcEvent();
        /** @var array $config */
        $config = $serviceManager->get('Config');
        /** @var array $routerConfig */
        $routerConfig = $config['router'] ?? array();
        $router = HttpRouter::factory($routerConfig);

        $event->setRouter($router);
        $event->setRouteMatch($routeMatch);
        $event->setRequest($request);
        $event->setResponse($response);

        $controller->setEvent($event);

        return $controller;
    }

    /**
     * Test get action Without ID
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetActionWithoutId(): void
    {
        $id = null;
        $variation = null;
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->onlyMethods(array('getReportName'))->getMock();

        $controller = $this->setUpController($documentServiceMock, $id, $variation);
        $response = $controller->getAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(417, $response->getStatusCode());
    }

    /**
     * Test get action With Missing Template
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetActionWithMissingTemplate(): void
    {
        $id = 1;
        $variation = null;
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->onlyMethods(array('getReportName'))->getMock();
        $documentServiceMock->expects($this->once())
            ->method('getReportName')
            ->will($this->throwException(new TemplateNotFoundException('Template not found')));

        $controller = $this->setUpController($documentServiceMock, $id, $variation);
        $response = $controller->getAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Test get action With unexpected Exception Being Thrown
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetActionWithUnexpectedExceptionBeingThrown(): void
    {
        $id = 1;
        $variation = null;
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->onlyMethods(array('getReportName'))->getMock();
        $documentServiceMock->expects($this->once())
            ->method('getReportName')
            ->will($this->throwException(new \Exception('Oh no, something went wrong')));

        $controller = $this->setUpController($documentServiceMock, $id, $variation);
        $response = $controller->getAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Test get action
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetActionHappyPath(): void
    {
        $id = 1;
        $variation = null;
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->onlyMethods(array('getReportName'))->getMock();
        $documentServiceMock->expects($this->once())
            ->method('getReportName')
            ->will($this->returnValue('ReportName.pdf'));

        $controller = $this->setUpController($documentServiceMock, $id, $variation);
        $response = $controller->getAction();

        $this->assertInstanceOf(JsonModel::class, $response);
        $this->assertEquals(array('report-name' => 'ReportName.pdf'), $response->getVariables());
    }
}
