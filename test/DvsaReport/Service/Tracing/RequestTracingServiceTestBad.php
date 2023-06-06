<?php

namespace DvsaReportModuleTest\DvsaReport\Service\Tracing;

use DvsaReport\Service\Tracing\RequestTracingService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Laminas\Http\Request;
use DvsaApplicationLogger\Log\Logger;

class RequestTracingServiceTestBad extends TestCase
{
    /** @var RequestTracingService|MockObject  */
    protected $service;
    protected $request;

    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder(Logger::class)->setMethods(['err'])->getMock();
        $this->request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->setMethods(['setHeaders', 'getHeaders'])->getMock();

        $this->service = new RequestTracingService($this->logger);
    }

    public function testInitializeTrace() {

        $request = new Request();
        $headers = $request->getHeaders()
            ->addHeaderLine('x-api-key', "value");
        $request->setHeaders($headers);

        $request = $this->service->initializeTrace($request);
        $this->service->log($request , "any");

        $this->assertEquals("value", $request->getHeader("x-api-key")->getFieldValue());
        $this->assertNotNull($request->getHeader(RequestTracingService::TRACE_ID_HEADER)->getFieldValue());
        $this->assertEquals("", $request->getHeader(RequestTracingService::PARENT_ID_HEADER)->getFieldValue());
        $this->assertNotNull($request->getHeader(RequestTracingService::SPAN_ID_HEADER)->getFieldValue());
    }
    
    public function testUpdateSpanId() {
        $request = new Request();
        $headers = $request->getHeaders()
            ->addHeaderLine('x-api-key', "value");
        $request->setHeaders($headers);

        $request = $this->service->initializeTrace($request);

        $oldHeaders = $request->getHeaders()->toArray();

        $request = $this->service->updateTracingHeader($request, RequestTracingService::SPAN_ID_HEADER, "asd");

        $updatedHeaders = $request->getHeaders()->toArray();

        $spanIdKey = RequestTracingService::SPAN_ID_HEADER;
        $this->assertNotEquals($oldHeaders[$spanIdKey], $updatedHeaders[$spanIdKey]);
        unset($oldHeaders[$spanIdKey]);
        unset($updatedHeaders[$spanIdKey]); // remove the spanId to easily verify rest of headers didn't change

        $this->assertEquals($oldHeaders, $updatedHeaders);
    }

    public function testCreate64BitId()
    {
        $id = $this->service->create64BitIdAsHex();

        $this->assertNotNull($id);
    }
}
