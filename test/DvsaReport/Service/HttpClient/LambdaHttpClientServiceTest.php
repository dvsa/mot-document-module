<?php

namespace DvsaReportModuleTest\DvsaReport\Service\HttpClient;

use DvsaReport\Service\HttpClient\LambdaHttpClientService;
use Laminas\Http\Response;
use PHPUnit\Framework\TestCase;

/**
 * LambdaHttpClientService Test
 *
 */
class LambdaHttpClientServiceTest extends TestCase
{
    /** @var LambdaHttpClientService  */
    protected $service;
    protected $client;
    protected $request;
    protected $logger;

    public function setUp(): void
    {
        $this->client = $this->getMockBuilder('\Laminas\Http\Client')->disableOriginalConstructor()->setMethods(['setAuth', 'setOptions', 'dispatch'])->getMock();
        $this->request = $this->getMockBuilder('\Laminas\Http\Request')->disableOriginalConstructor()->setMethods(['setUri', 'getUriString'])->getMock();
        $this->logger = $this->getMockBuilder('\Laminas\Log\Logger')->setMethods(['info'])->getMock();
        $this->service = new LambdaHttpClientService();

        $this->service->setClient($this->client);
        $this->service->setRequest($this->request);
        $this->service->setLogger($this->logger);
    }

    public function testGetClient()
    {
        $this->assertSame($this->client, $this->service->getClient());
    }

    public function testGetLogger()
    {
        $this->assertSame($this->logger, $this->service->getLogger());
    }

    public function testGetRequest()
    {
        $this->assertSame($this->request, $this->service->getRequest());
    }

    public function testSetOptionsProxiesThroughToClient()
    {
        $this->client->expects($this->once())
            ->method('setOptions')
            ->with('foo-bar');

        $this->service->setOptions('foo-bar');
    }

    public function testDispatchIssuesRequest()
    {
        $response = (new Response())->setContent('foo');

        $this->client->expects($this->once())
            ->method('dispatch')
            ->with($this->request)
            ->willReturn($response);

        $this->assertEquals('foo', $this->service->dispatch()->getContent());
    }

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
