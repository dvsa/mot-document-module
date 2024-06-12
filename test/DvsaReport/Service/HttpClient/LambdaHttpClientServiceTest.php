<?php

namespace DvsaReportModuleTest\DvsaReport\Service\HttpClient;

use DvsaReport\Service\HttpClient\LambdaHttpClientService;
use Laminas\Http\Response;
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

    /** @var MockObject&\Laminas\Http\Client */
    protected $client;

    /** @var MockObject&\Laminas\Http\Request */
    protected $request;

    /** @var MockObject&\Laminas\Http\Response */
    protected $response;

    /** @var MockObject&\Laminas\Log\Logger */
    protected $logger;


    public function setUp(): void
    {
        $this->client = $this->getMockBuilder(\Laminas\Http\Client::class)->disableOriginalConstructor()->onlyMethods(['setAuth', 'setOptions', 'dispatch'])->getMock();
        $this->request = $this->getMockBuilder(\Laminas\Http\Request::class)->disableOriginalConstructor()->onlyMethods(['setUri', 'getUriString'])->getMock();
        $this->logger = $this->getMockBuilder(\Laminas\Log\Logger::class)->disableOriginalConstructor()->onlyMethods(['info'])->getMock();
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
