<?php

/**
 * Report Name Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocumentModuleTest\DvsaDocument\Controller;

use DvsaDocument\Factory\Controller\ReportNameControllerFactory;
use DvsaDocument\Service\Document\DocumentService;
use PHPUnit\Framework\TestCase;
use DvsaDocumentModuleTest\Bootstrap;
use DvsaDocument\Controller\ReportNameController;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Router\RouteMatch;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\TreeRouteStack as HttpRouter;
use DvsaDocument\Exceptions\TemplateNotFoundException;
use Laminas\View\Model\JsonModel;

/**
 * Report Name Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReportNameControllerTest extends TestCase
{
    /**
     * @param \PHPUnit\Framework\MockObject\MockObject&DocumentService $documentServiceMock
     * @param int|null $id
     * @param mixed $variation
     *
     * @return ReportNameController
     */
    private function setUpController($documentServiceMock, $id, $variation)
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
        /** @var array */
        $config = $serviceManager->get('Config');
        /** @var array */
        $routerConfig = isset($config['router']) ? $config['router'] : array();
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
     */
    public function testGetActionWithoutId()
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
     */
    public function testGetActionWithMissingTemplate()
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
     */
    public function testGetActionWithUnexpectedExceptionBeingThrown()
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
     */
    public function testGetActionHappyPath()
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
