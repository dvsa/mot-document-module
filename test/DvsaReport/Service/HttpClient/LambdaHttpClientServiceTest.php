<?php

declare(strict_types=1);

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
final class LambdaHttpClientServiceTest extends TestCase
{
    protected LambdaHttpClientService $service;

    protected MockObject&Client $client;

    protected MockObject&Request $request;

    protected MockObject&Logger $logger;

    #[\Override]
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

    public function testGetClient(): void
    {
        $this->assertSame($this->client, $this->service->getClient());
    }

    public function testGetLogger(): void
    {
        $this->assertSame($this->logger, $this->service->getLogger());
    }

    public function testGetRequest(): void
    {
        $this->assertSame($this->request, $this->service->getRequest());
    }

    public function testSetOptionsProxiesThroughToClient(): void
    {
        $this->client->expects($this->once())
            ->method('setOptions')
            ->with(['foo-bar']);

        $this->service->setOptions(['foo-bar']);
    }

    public function testDispatchIssuesRequest(): void
    {
        $response = (new Response())->setContent('foo');

        $this->client->expects($this->once())
            ->method('dispatch')
            ->with($this->request)
            ->willReturn($response);

        $this->assertEquals('foo', $this->service->dispatch()->getContent());
    }

    public function testDispatchLogsUrl(): void
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
