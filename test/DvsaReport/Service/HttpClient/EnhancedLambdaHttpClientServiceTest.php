<?php

/**
 * Created by PhpStorm.
 * User: radoslawl
 * Date: 19/02/2018
 * Time: 14:47
 */

declare(strict_types=1);

namespace DvsaReportModuleTest\DvsaReport\Service\HttpClient;

use DvsaReport\Service\HttpClient\EnhancedLambdaHttpClientService;
use Laminas\Http\Response;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Laminas\Http\Client;
use Laminas\Http\Request;
use DvsaLogger\Logger\MotLogger;

final class EnhancedLambdaHttpClientServiceTest extends TestCase
{
    protected EnhancedLambdaHttpClientService $wrapper;

    protected MockObject&Client $client;

    protected MockObject&Request $request;

    protected MockObject&Response $response;

    protected MockObject&MotLogger $logger;

    protected int $maxAttemptCount;

    #[\Override]
    public function setUp(): void
    {
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['setAuth', 'setOptions', 'dispatch'])->getMock();
        $this->request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->onlyMethods(['setUri', 'getUriString'])->getMock();
        $this->response = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->onlyMethods(['getStatusCode', 'getBody', '__toString'])->getMock();
        $this->logger = $this->getMockBuilder(MotLogger::class)->disableOriginalConstructor()->onlyMethods(['info', 'warn'])->getMock();

        $this->maxAttemptCount = 3;
        $this->wrapper = new EnhancedLambdaHttpClientService($this->maxAttemptCount, 0);
        $this->wrapper->setClient($this->client);
        $this->wrapper->setRequest($this->request);
        $this->wrapper->setLogger($this->logger);
    }

    public function test200Response(): void
    {
        $this->response->method('getStatusCode')
            ->willReturn(Response::STATUS_CODE_200);

        $this->client->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->response);

        $r = $this->wrapper->dispatch();

        $this->assertEquals($r->getStatusCode(), 200);
    }

    /**
     * @dataProvider providerUnretriableCodes
     *
     * @param int $statusCode
     *
     * @return void
     */
    public function testNoneRetriableCodes($statusCode)
    {
        $this->expectException(\Exception::class);
        $this->response->method('getStatusCode')
            ->willReturn($statusCode);

        $this->client->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->response);

        $this->wrapper->dispatch();
    }

    public function providerUnretriableCodes(): array
    {
        // test with this values
        return array(
            array(Response::STATUS_CODE_400),
            array(Response::STATUS_CODE_500)
        );
    }

    /**
     * @dataProvider providerRetriableCodes
     */
    public function testRetriableCodes(int $statusCode): void
    {
        $this->response->method('getStatusCode')
            ->willReturn($statusCode);

        $this->client->expects($this->exactly($this->maxAttemptCount))
            ->method('dispatch')
            ->willReturn($this->response);

        try {
            $this->wrapper->dispatch();
        } catch (\Exception $e) {
            $this->assertStringContainsString('Getting report failed after 3 attempts', $e->getMessage());
        }
    }

    public function providerRetriableCodes(): array
    {
        // test with this values
        return array(
            array(Response::STATUS_CODE_429),
            array(Response::STATUS_CODE_503),
            array(Response::STATUS_CODE_504)
        );
    }

    /**
     * @throws \Exception
     */
    public function test200After429(): void
    {
        $this->response->method('getStatusCode')
            ->will($this->onConsecutiveCalls(Response::STATUS_CODE_429, Response::STATUS_CODE_200));
        $this->response->method('__toString')
            ->willReturn("asd");

        $this->client->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturn($this->response);

        $this->wrapper->dispatch();
    }

    /**
     * @throws \Exception
     */
    public function test200After429and429(): void
    {
        $this->response->method('getStatusCode')
            ->will($this->onConsecutiveCalls(Response::STATUS_CODE_429, Response::STATUS_CODE_429, Response::STATUS_CODE_200));
        $this->response->method('__toString')
            ->will($this->onConsecutiveCalls("first", "second", "third"));

        $this->client->expects($this->exactly(3))
            ->method('dispatch')
            ->willReturn($this->response);

        $r = $this->wrapper->dispatch();

        $this->assertEquals("third", $r);
    }

    public function test500After429(): void
    {
        $this->response->method('getStatusCode')
            ->will($this->onConsecutiveCalls(Response::STATUS_CODE_429, Response::STATUS_CODE_500));
        $this->response->method('__toString')
            ->will($this->onConsecutiveCalls("first", "second"));

        $this->client->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturn($this->response);

        try {
            $this->wrapper->dispatch();
        } catch (\Exception $e) {
            $this->assertStringContainsString("second", $e->getMessage());
        }
    }
}
