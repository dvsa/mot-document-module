<?php

namespace DvsaReportModuleTest\DvsaReport\Service\HttpClient;

use DvsaReport\Service\HttpClient\LambdaHttpClientService;
use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Log\Logger;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * LambdaHttpClientService Test
 *
 */
class LambdaHttpClientServiceTest extends TestCase
{
    /** @var LambdaHttpClientService  */
    protected $service;

    /** @var MockObject&Client */
    protected $client;

    /** @var MockObject&Request */
    protected $request;

    /** @var MockObject&Response */
    protected $response;

    /** @var MockObject&Logger */
    protected $logger;


    public function setUp(): void
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['setAuth', 'setOptions', 'dispatch'])->getMock();
        $this->request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->onlyMethods(['setUri', 'getUriString'])->getMock();
        $this->logger = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->onlyMethods(['info'])->getMock();
        $this->service = new LambdaHttpClientService();

        $this->service->setClient($this->client);
        $this->service->setRequest($this->request);
        $this->service->setLogger($this->logger);
    }

    /**
     * @return void
     */
    public function testGetClient()
    {
        $this->assertSame($this->client, $this->service->getClient());
    }

    /**
     * @return void
     */
    public function testGetLogger()
    {
        $this->assertSame($this->logger, $this->service->getLogger());
    }

    /**
     * @return void
     */
    public function testGetRequest()
    {
        $this->assertSame($this->request, $this->service->getRequest());
    }

    /**
     * @return void
     */
    public function testSetOptionsProxiesThroughToClient()
    {
        $this->client->expects($this->once())
            ->method('setOptions')
            ->with(['foo-bar']);

        $this->service->setOptions(['foo-bar']);
    }

    /**
     * @return void
     */
    public function testDispatchIssuesRequest()
    {
        $response = (new Response())->setContent('foo');

        $this->client->expects($this->once())
            ->method('dispatch')
            ->with($this->request)
            ->willReturn($response);

        $this->assertEquals('foo', $this->service->dispatch()->getContent());
    }

    /**
     * @return void
     */
    public function testDispatchLogsUrl()
    {
        $this->client->method('dispatch')
            ->willReturn(new Response());

        $this->request->method('getUriString')
            ->willReturn('http://mock-lambda.com');

        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->equalTo('Lambda document generator service call. Url: "http://mock-lambda.com"'));

        $this->service->dispatch();
    }
}
