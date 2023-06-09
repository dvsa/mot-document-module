<?php

/**
 * Document Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace DvsaDocumentModuleTest\DvsaDocument\Controller;

use DvsaDocument\Service\Document\DocumentService;
use PHPUnit\Framework\TestCase;
use DvsaDocumentModuleTest\Bootstrap;
use DvsaDocument\Controller\DocumentController;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Router\RouteMatch;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\TreeRouteStack as HttpRouter;
use DvsaDocument\Exceptions\TemplateNotFoundException;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\JsonModel;

/**
 * Document Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DocumentControllerTest extends TestCase
{
    private function setUpController($documentServiceMock, $id = null, $post = array())
    {
        $controller = new DocumentController($documentServiceMock);
        if (is_array($post) && count($post)) {
            $params = new Parameters();
            foreach ($post as $key => $value) {
                $params->set($key, $value);
            }
            $controller->getRequest()
                ->setMethod('POST')
                ->setPost($params);
        }
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        $request = new Request();
        $response = new Response();
        if (!is_null($id)) {
            $routeMatch = new RouteMatch(array('id' => $id));
        } else {
            $routeMatch = new RouteMatch(array());
        }

        $event = new MvcEvent();
        $config = $serviceManager->get('Config');
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
     * Test delete action Without ID
     */
    public function testDeleteActionWithoutId()
    {
        $id = null;
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->setMethods(array('deleteSnapshot'))->getMock();

        $controller = $this->setUpController($documentServiceMock, $id);
        $response = $controller->deleteAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(417, $response->getStatusCode());
    }

    /**
     * Test delete action With ID
     */
    public function testDeleteActionWithId()
    {
        $id = -1;
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->setMethods(array('deleteSnapshot'))->getMock();

        $controller = $this->setUpController($documentServiceMock, $id);
        $response = $controller->deleteAction();

        $this->assertInstanceOf(JsonModel::class, $response);
        $decoded = $response->getVariables();
        $this->assertEquals($id, $decoded['id']);
    }

    /**
     * Test delete action with unexpected Exception Being Thrown
     */
    public function testDeleteActionWithUnexpectedExceptionBeingThrown()
    {
        $id = -1;
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->setMethods(array('deleteSnapshot'))->getMock();
        $documentServiceMock->expects($this->once())
            ->method('deleteSnapshot')
            ->will($this->throwException(new \Exception('Oh no, something went wrong')));

        $controller = $this->setUpController($documentServiceMock, $id);
        $response = $controller->deleteAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }


    /**
     * Test create action With Missing Template name
     */
    public function testCreateActionWithMissingTemplateName()
    {
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->setMethods(array('createSnapshot'))->getMock();
        $controller = $this->setUpController($documentServiceMock, null, array('data' => array('foo' => 'foo')));
        $response = $controller->createAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Test create action With Missing document
     */
    public function testCreateActionWithMissingDocument()
    {
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->setMethods(array('createSnapshot'))->getMock();
        $controller = $this->setUpController($documentServiceMock, null, array('template' => 'name'));
        $response = $controller->createAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Test create action with template not found exception beeing thrown
     */
    public function testCreateActionWithTemplateNotFound()
    {
        $templateName = 'not found';
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->setMethods(array('createSnapshot'))->getMock();
        $documentServiceMock->expects($this->once())
            ->method('createSnapshot')
            ->will($this->throwException(new TemplateNotFoundException('Template not found')));

        $controller = $this->setUpController(
            $documentServiceMock,
            null,
            ['template' => $templateName, 'data' => ['foo' => 'foo'], 'userId' => 1]
        );
        $response = $controller->createAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Test create action with unexpected exception beeing thrown
     */
    public function testCreateActionWithUnexpectedExceptionBeingThrown()
    {
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->setMethods(array('createSnapshot'))->getMock();
        $documentServiceMock->expects($this->once())
            ->method('createSnapshot')
            ->will($this->throwException(new \Exception('Unexpected error')));

        $controller = $this->setUpController(
            $documentServiceMock,
            null,
            ['template' => 'name', 'data' => ['foo' => 'foo'], 'userId' => 1]
        );
        $response = $controller->createAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Test create action
     */
    public function testCreateAction()
    {
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->setMethods(array('createSnapshot'))->getMock();
        $documentServiceMock->expects($this->once())
            ->method('createSnapshot')
            ->will($this->returnValue(1));

        $controller = $this->setUpController(
            $documentServiceMock,
            null,
            ['template' => 'name', 'data' => ['foo' => 'foo'], 'userId' => 1]
        );
        $response = $controller->createAction();

        $this->assertInstanceOf('\Laminas\View\Model\JsonModel', $response);
        $decoded = $response->getVariables();
        $this->assertEquals($decoded['id'], 1);
    }
}
