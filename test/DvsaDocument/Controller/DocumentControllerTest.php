<?php

/**
 * Document Controller Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace DvsaDocumentModuleTest\DvsaDocument\Controller;

use Doctrine\ORM\EntityManagerInterface;
use DvsaDocument\Service\Document\DocumentService;
use Laminas\Router\RouteStackInterface;
use PHPUnit\Framework\TestCase;
use DvsaDocumentModuleTest\TestBootstrap as Bootstrap;
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
final class DocumentControllerTest extends TestCase
{
    /**
     * @param DocumentService $documentServiceMock
     * @param int $id
     * @param mixed $post
     *
     * @return DocumentController
     */
    private function setUpController($documentServiceMock, $id = null, $post = array())
    {
        $controller = new DocumentController($documentServiceMock);
        if (is_array($post) && count($post)) {
            $params = new Parameters();
            foreach ($post as $key => $value) {
                $params->set($key, $value);
            }
            /** @var Request */
            $controllerRequest = $controller->getRequest();
            $controllerRequest->setMethod('POST')
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
        /** @var array $config*/
        $config = $serviceManager->get('Config');
        /** @var array $routerConfig*/
        $routerConfig = $config['router'] ?? array();
        $router = HttpRouter::factory($routerConfig);

        /** @var RouteStackInterface $router */
        $event->setRouter($router);
        $event->setRouteMatch($routeMatch);
        $event->setRequest($request);
        $event->setResponse($response);

        $controller->setEvent($event);

        return $controller;
    }

    /**
     * Test delete action Without ID
     *
     * @return void
     */
    public function testDeleteActionWithoutId(): void
    {
        $id = null;
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->onlyMethods(array('deleteSnapshot'))->getMock();

        $controller = $this->setUpController($documentServiceMock, $id);
        $response = $controller->deleteAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(417, $response->getStatusCode());
    }

    /**
     * Test delete action With ID
     *
     * @return void
     */
    public function testDeleteActionWithId(): void
    {
        $id = -1;
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->onlyMethods(array('deleteSnapshot'))->getMock();

        $controller = $this->setUpController($documentServiceMock, $id);
        $response = $controller->deleteAction();

        /** @psalm-suppress DeprecatedClass BL-19119*/
        $this->assertInstanceOf(JsonModel::class, $response);
        $decoded = $response->getVariables();
        /** @var array<string, mixed> $decoded */
        $this->assertEquals($id, $decoded['id']);
    }

    /**
     * Test delete action with unexpected Exception Being Thrown
     *
     * @return void
     */
    public function testDeleteActionWithUnexpectedExceptionBeingThrown(): void
    {
        $id = -1;
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->onlyMethods(array('deleteSnapshot'))->getMock();
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
     *
     * @return void
     */
    public function testCreateActionWithMissingTemplateName(): void
    {
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->onlyMethods(array('createSnapshot'))->getMock();
        $controller = $this->setUpController($documentServiceMock, null, array('data' => array('foo' => 'foo')));
        $response = $controller->createAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Test create action With Missing document
     *
     * @return void
     */
    public function testCreateActionWithMissingDocument(): void
    {
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->onlyMethods(array('createSnapshot'))->getMock();
        $controller = $this->setUpController($documentServiceMock, null, array('template' => 'name'));
        $response = $controller->createAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Test create action with template not found exception beeing thrown
     *
     * @return void
     */
    public function testCreateActionWithTemplateNotFound(): void
    {
        $templateName = 'not found';
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->onlyMethods(array('createSnapshot'))->getMock();
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
     *
     * @return void
     */
    public function testCreateActionWithUnexpectedExceptionBeingThrown(): void
    {
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->onlyMethods(array('createSnapshot'))->getMock();
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
     *
     * @return void
     */
    public function testCreateAction(): void
    {
        $documentServiceMock = $this->getMockBuilder(DocumentService::class)->disableOriginalConstructor()->onlyMethods(array('createSnapshot'))->getMock();
        $documentServiceMock->expects($this->once())
            ->method('createSnapshot')
            ->will($this->returnValue(1));

        $controller = $this->setUpController(
            $documentServiceMock,
            null,
            ['template' => 'name', 'data' => ['foo' => 'foo'], 'userId' => 1]
        );
        $response = $controller->createAction();

        /** @psalm-suppress DeprecatedClass BL-19119*/
        $this->assertInstanceOf(JsonModel::class, $response);
        $decoded = $response->getVariables();
        /** @var array<string, mixed> $decoded */
        $this->assertEquals(1, $decoded['id']);
    }
}
