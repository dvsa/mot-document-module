<?php

/**
 * LambdaReportService Test
 */

namespace DvsaReportModuleTest\DvsaReport\Service\Report;

use DvsaDocument\Entity\Document;
use DvsaReport\Service\HttpClient\EnhancedLambdaHttpClientService;
use DvsaReport\Service\Report\LambdaReportService;
use DvsaReport\Model\Report;
use Laminas\Http\Response;
use Laminas\Http\Headers;
use DvsaReport\Service\Pdf\PdfRenderer;
use DvsaReport\Exceptions\ReportNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Laminas\Log\Logger;

/**
 * LambdaReportService Test
 */
class LambdaReportServiceTest extends TestCase
{
    /** @var LambdaReportService */
    protected $service;

    /** @var EnhancedLambdaHttpClientService&MockObject   */
    protected $client;

    /**
     * @var PdfRenderer&MockObject
     */
    protected $stubPdfRenderer;

    /** @var MockObject&Logger */
    protected $logger;

    public function setUp(): void
    {

        $this->stubPdfRenderer = $this->getMockBuilder(PdfRenderer::class)->disableOriginalConstructor()->onlyMethods(['buildPdfParameters'])->getMock();

        $this->client = $this->getMockBuilder(EnhancedLambdaHttpClientService::class)->disableOriginalConstructor()->getMock();

        $this->logger = $this->getMockBuilder(Logger::class)->getMock();

        $this->service = new LambdaReportService($this->stubPdfRenderer, $this->client);
    }

    /**
     * @return void
     */
    public function testGetHttpClient()
    {
        $this->assertSame($this->client, $this->service->getHttpClient());
    }

    /**
     * @return void
     */
    public function testGetDocumentThrowsExpectedExceptionWithFailedResponse()
    {
        /** @var MockObject&Response */
        $response = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->onlyMethods(['isSuccess', 'getReasonPhrase'])->getMock();

        $response->expects($this->once())
            ->method('isSuccess')
            ->will($this->returnValue(false));

        $response->expects($this->once())
            ->method('getReasonPhrase')
            ->will($this->returnValue('404 Not Found'));

        try {
            $this->service->getReportFromResponse($response);
        } catch (ReportNotFoundException $ex) {
            $this->assertEquals('404 Not Found', $ex->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    /**
     * @return void
     */
    public function testGetDocumentWhenSuccessful()
    {
        /** @var MockObject&Response */
        $response = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->onlyMethods(['isSuccess', 'getHeaders', 'getBody'])->getMock();
        $headers = $this->getMockBuilder(\stdClass::class)->disableOriginalConstructor()->addmethods(['get'])->getMock();

        $response->expects($this->once())
            ->method('isSuccess')
            ->will($this->returnValue(true));

        $response->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue('body content of pdf'));

        $response->expects($this->once())
            ->method('getHeaders')
            ->will($this->returnValue($headers));

        $headerMap = [
            $this->mockFieldValue('Content-Type', 'application/pdf'),
            $this->mockFieldValue('Content-Length', 1234)
        ];

        $headers->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($headerMap));

        $document = $this->service->getReportFromResponse($response);
        $this->assertInstanceOf(Report::class, $document);

        $this->assertEquals('body content of pdf', $document->getData());
        $this->assertEquals('application/pdf', $document->getMimeType());
        $this->assertEquals(1234, $document->getSize());
    }

    /**
     * @return void
     */
    public function testGetVt20W()
    {
        /** @var LambdaReportService&MockObject $service  */
        $service = $this->getMockBuilder(
            LambdaReportService::class
        )
            ->setConstructorArgs([$this->stubPdfRenderer, $this->client])
            ->onlyMethods(['getReport'])
            ->getMock();

        /** @var Document&MockObject */
        $mockSnap = $this->getMockBuilder(Document::class)->disableOriginalConstructor()->onlyMethods(['getDocumentContent'])->getMock();
        $mockSnap->expects($this->any())
            ->method('getDocumentContent')
            ->will($this->returnValue(["TestNumber" => "numberVT20W"]));

        $this->stubPdfRenderer
            ->expects($this->once())
            ->method('buildPdfParameters')
            ->with($mockSnap)
            ->willReturnOnConsecutiveCalls(["TestNumber" => "numberVT20W"]);

        $service->expects($this->exactly(1))
            ->method('getReport')
            ->with("MOT/VT20W.pdf", ["DATA" => '{"TestNumber":"numberVT20W"}']);

        $service->getMergedPdfReports(
            false,
            [
                [
                    'documentId' => 2,
                    'reportName' => 'MOT/VT20W.pdf',
                    'runtimeParams' => ["snapshotData" => $mockSnap]
                ]
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetVt30W()
    {
        /** @var LambdaReportService&MockObject $service  */
        $service = $this->getMockBuilder(
            LambdaReportService::class
        )
            ->setConstructorArgs([$this->stubPdfRenderer, $this->client])
            ->onlyMethods(['getReport'])
            ->getMock();

        $mockSnap = $this->getMockBuilder(Document::class)->disableOriginalConstructor()->onlyMethods(['getDocumentContent'])->getMock();
        $mockSnap->expects($this->any())
            ->method('getDocumentContent')
            ->will($this->returnValue(["TestNumber" => "numberVT30W"]));

        $this->stubPdfRenderer->expects($this->exactly(1))
            ->method('buildPdfParameters')
            ->with($mockSnap)
            ->willReturnOnConsecutiveCalls(["TestNumber" => "numberVT30W"]);

        $service->expects($this->exactly(1))
            ->method('getReport')
            ->with("MOT/VT30W.pdf", ["FAIL_DATA" => '{"TestNumber":"numberVT30W"}']);

        $service->getMergedPdfReports(
            false,
            [
                [
                    'documentId' => 2,
                    'reportName' => 'MOT/VT30W.pdf',
                    'runtimeParams' => ["snapshotData" => $mockSnap]
                ]
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetPRS()
    {
        /** @var LambdaReportService&MockObject $service  */
        $service = $this->getMockBuilder(
            LambdaReportService::class
        )
            ->setConstructorArgs([$this->stubPdfRenderer, $this->client])
            ->onlyMethods(['getReport'])
            ->getMock();

        $mockSnapVT20 = $this->getMockBuilder(Document::class)->disableOriginalConstructor()->onlyMethods(['getDocumentContent'])->getMock();
        $mockSnapVT20->expects($this->any())
            ->method('getDocumentContent')
            ->will($this->returnValue(["TestNumber" => "numberVT20"]));

        $mockSnapVT30 = $this->getMockBuilder(Document::class)->disableOriginalConstructor()->onlyMethods(['getDocumentContent'])->getMock();
        $mockSnapVT30->expects($this->any())
            ->method('getDocumentContent')
            ->will($this->returnValue(["TestNumber" => "numberVT30"]));

        $this->stubPdfRenderer->expects($this->exactly(2))
            ->method('buildPdfParameters')
            ->willReturnOnConsecutiveCalls(["TestNumber" => "numberVT20"], ["TestNumber" => "numberVT30"]);

        $service->expects($this->exactly(1))
            ->method('getReport')
            ->with("MOT/PRS.pdf", ["DATA" => '{"TestNumber":"numberVT20"}', "FAIL_DATA" => '{"TestNumber":"numberVT30"}']);

        $service->getMergedPdfReports(
            true,
            [
                [
                    'documentId' => 2,
                    'reportName' => 'MOT/VT20.pdf',
                    'runtimeParams' => ["snapshotData" => $mockSnapVT20]
                ], [
                    'documentId' => 3,
                    'reportName' => 'MOT/VT30.pdf',
                    'runtimeParams' => ["snapshotData" => $mockSnapVT30]
                ]
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetPRSW()
    {
        /** @var LambdaReportService&MockObject $service  */
        $service = $this->getMockBuilder(
            LambdaReportService::class
        )
            ->setConstructorArgs([$this->stubPdfRenderer, $this->client])
            ->onlyMethods(['getReport'])
            ->getMock();

        $this->stubPdfRenderer->expects($this->any())
            ->method('buildPdfParameters')
            ->will($this->returnValue(["TestNumber" => "number"]));

        $mockSnap = $this->getMockBuilder(Document::class)->disableOriginalConstructor()->onlyMethods(['getDocumentContent'])->getMock();
        $mockSnap->expects($this->any())
            ->method('getDocumentContent')
            ->will($this->returnValue(["TestNumber" => "number"]));

        $service->expects($this->exactly(1))
            ->method('getReport')
            ->with("MOT/PRSW.pdf", ["DATA" => '{"TestNumber":"number"}', "FAIL_DATA" => '{"TestNumber":"number"}']);

        $service->getMergedPdfReports(
            true,
            [
                [
                    'documentId' => 2,
                    'reportName' => 'MOT/VT20W.pdf',
                    'runtimeParams' => ["snapshotData" => $mockSnap]
                ], [
                    'documentId' => 3,
                    'reportName' => 'MOT/VT30W.pdf',
                    'runtimeParams' => ["snapshotData" => $mockSnap]
                ]
            ]
        );
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return array
     */
    protected function mockFieldValue($key, $value)
    {
        $field = $this->getMockBuilder(\stdClass::class)->disableOriginalConstructor()->addMethods(['getFieldValue'])->getMock();
        $field->expects($this->any())
            ->method('getFieldValue')
            ->will($this->returnValue($value));

        return [$key, $field];
    }

    /**
     * @param string $resourceName
     *
     * @return MockObject&Response
     */
    protected function getMockReport($resourceName)
    {
        $headerMock = $this->getMockBuilder(Headers::class)->disableOriginalConstructor()->onlyMethods(['toString'])->getMock();
        $headerMock->expects($this->any())
            ->method('toString')
            ->will($this->returnValue('Content-Type: 1234'));

        $mock = $this->getMockBuilder(Response::class)->disableOriginalConstructor()->onlyMethods(['getHeaders', 'getContent', 'getStatusCode'])->getMock();
        $mock->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(file_get_contents(__DIR__ . '/../../Resources/' . $resourceName)));

        $mock->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(200);

        $mock->expects($this->any())
            ->method('getHeaders')
            ->will($this->returnValue($headerMock));

        return $mock;
    }
}
