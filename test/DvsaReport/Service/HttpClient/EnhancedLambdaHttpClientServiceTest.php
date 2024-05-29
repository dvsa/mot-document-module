<?php
/**
 * Created by PhpStorm.
 * User: radoslawl
 * Date: 19/02/2018
 * Time: 14:47
 */


namespace DvsaReportModuleTest\DvsaReport\Service\HttpClient;

use DvsaReport\Service\HttpClient\EnhancedLambdaHttpClientService;
use Laminas\Http\Response;
use PHPUnit\Framework\TestCase;

class EnhancedLambdaHttpClientServiceTest extends TestCase
{
    /** @var EnhancedLambdaHttpClientService  */
    protected $wrapper;
    protected $client;
    protected $maxAttemptCount;
    protected $request;
    protected $response;
    protected $logger;

    public function setUp(): void
    {
        $this->client = $this->getMockBuilder('\Laminas\Http\Client')->disableOriginalConstructor()->setMethods(['setAuth', 'setOptions', 'dispatch'])->getMock();
        $this->request = $this->getMockBuilder('\Laminas\Http\Request')->disableOriginalConstructor()->setMethods(['setUri', 'getUriString'])->getMock();
        $this->response = $this->getMockBuilder('\Laminas\Http\Response')->disableOriginalConstructor()->setMethods(['getStatusCode', 'getBody', '__toString'])->getMock();
        $this->logger = $this->getMockBuilder('\Laminas\Log\Logger')->disableOriginalConstructor()->setMethods(['info', 'warn'])->getMock();

        $this->maxAttemptCount = 3;
        $this->wrapper = new EnhancedLambdaHttpClientService($this->maxAttemptCount, 0);
        $this->wrapper->setClient($this->client);
        $this->wrapper->setRequest($this->request);
        $this->wrapper->setLogger($this->logger);
    }

    public function test200Response()
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
     */
    public function testUnretriableCodes($statusCode)
    {
        $this->expectException(\Exception::class);
        $this->response->method('getStatusCode')
            ->willReturn($statusCode);

        $this->client->expects($this->once())
            ->method('dispatch')
            ->willReturn($this->response);

        $this->wrapper->dispatch();
    }

    public function providerUnretriableCodes() {
        // test with this values
        return array(
            array(Response::STATUS_CODE_400),
            array(Response::STATUS_CODE_500)
        );
    }

    /**
     * @dataProvider providerRetriableCodes
     */
    public function testRetriableCodes($statusCode)
    {
        $this->response->method('getStatusCode')
            ->willReturn($statusCode);

        $this->client->expects($this->exactly($this->maxAttemptCount))
            ->method('dispatch')
            ->willReturn($this->response);

        try {
            $this->wrapper->dispatch();
        } catch(\Exception $e) {
            $this->assertStringContainsString('Getting report failed after 3 attempts', $e->getMessage());
        }
    }

    public function providerRetriableCodes() {
        // test with this values
        return array(
            array(Response::STATUS_CODE_429),
            array(Response::STATUS_CODE_503),
            array(Response::STATUS_CODE_504)
        );
    }

    public function test200After429()
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

    public function test200After429and429()
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

    public function test500After429()
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
        } catch(\Exception $e) {
            $this->assertStringContainsString("second", $e->getMessage());
        }
    }
}
