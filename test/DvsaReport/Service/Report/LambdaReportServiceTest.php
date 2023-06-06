<?php

/**
 * LambdaReportService Test
 */
namespace DvsaReportModuleTest\DvsaReport\Service\Report;

use DvsaReport\Service\HttpClient\LambdaHttpClientService;
use DvsaReport\Service\HttpClient\EnhancedLambdaHttpClientService;
use DvsaReport\Service\Report\LambdaReportService;
use Laminas\Http\Response as ZendResponse;
use DvsaReport\Service\Pdf\PdfRenderer;
use DvsaReport\Exceptions\ReportNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
/**
 * LambdaReportService Test
 */
class LambdaReportServiceTest extends TestCase
{
    /** @var LambdaReportService|MockObject  */
    protected $service;

    /** @var \DvsaReport\Service\HttpClient\EnhancedLambdaHttpClientService|MockObject   */
    protected $client;

    /**
     * @var PdfRenderer
     */
    protected $stubPdfRenderer;

    public function setUp(): void
    {

        $this->stubPdfRenderer = $this->getMockBuilder(PdfRenderer::class)->disableOriginalConstructor()->setMethods(['buildPdfParameters'])->getMock();

        $this->client = $this->getMockBuilder(EnhancedLambdaHttpClientService::class)->disableOriginalConstructor()->getMock();

        $this->logger = $this->getMockBuilder('\Laminas\Log\Logger')->getMock();

        $this->service = new LambdaReportService($this->stubPdfRenderer, $this->client);
    }

    public function testGetHttpClient()
    {
        $this->assertSame($this->client, $this->service->getHttpClient());
    }

    public function testGetDocumentThrowsExpectedExceptionWithFailedResponse()
    {
        $response = $this->getMockBuilder('\Laminas\Http\Response')->disableOriginalConstructor()->setMethods(['isSuccess', 'getReasonPhrase'])->getMock();

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

    public function testGetDocumentWhenSuccessful()
    {
        $response = $this->getMockBuilder('\Laminas\Http\Response')->disableOriginalConstructor()->setMethods(['isSuccess', 'getHeaders', 'getBody'])->getMock();
        $headers = $this->getMockBuilder('\stdClass')->disableOriginalConstructor()->setMethods(['get'])->getMock();

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
        $this->assertInstanceOf('\DvsaReport\Model\Report', $document);

        // @TODO re-implement ASAP
        //$this->assertEquals('a-template-v1', $document->getName());
        $this->assertEquals('body content of pdf', $document->getData());
        $this->assertEquals('application/pdf', $document->getMimeType());
        $this->assertEquals(1234, $document->getSize());
    }

    public function testGetVt20W()
    {
        /** @var LambdaReportService|MockObject $service  */
        $service = $this->getMockBuilder(
            LambdaReportService::class)
            ->setConstructorArgs([$this->stubPdfRenderer, $this->client])
            ->onlyMethods(['getReport'])
            ->getMock();

        $mockSnap = $this->getMockBuilder('\DvsaDocument\Entity\Document')->disableOriginalConstructor()->onlyMethods(['getDocumentContent'])->getMock();
        $mockSnap->expects($this->any())
            ->method('getDocumentContent')
            ->will($this->returnValue(["TestNumber" => "numberVT20W"]));

        $this->stubPdfRenderer->expects($this->exactly(1))
            ->method('buildPdfParameters')
            ->withConsecutive([$mockSnap])
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

    public function testGetVt30W()
    {
        /** @var LambdaReportService|MockObject $service  */
        $service = $this->getMockBuilder(
            LambdaReportService::class)
            ->setConstructorArgs([$this->stubPdfRenderer, $this->client])
            ->onlyMethods(['getReport'])
            ->getMock();

        $mockSnap = $this->getMockBuilder('\DvsaDocument\Entity\Document')->disableOriginalConstructor()->setMethods(['getDocumentContent'])->getMock();
        $mockSnap->expects($this->any())
            ->method('getDocumentContent')
            ->will($this->returnValue(["TestNumber" => "numberVT30W"]));

        $this->stubPdfRenderer->expects($this->exactly(1))
            ->method('buildPdfParameters')
            ->withConsecutive([$mockSnap])
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

    public function testGetPRS()
    {
        /** @var LambdaReportService|MockObject $service  */
        $service = $this->getMockBuilder(
            LambdaReportService::class)
            ->setConstructorArgs([$this->stubPdfRenderer, $this->client])
            ->onlyMethods(['getReport'])
            ->getMock();

        $mockSnapVT20 = $this->getMockBuilder('\DvsaDocument\Entity\Document')->disableOriginalConstructor()->setMethods(['getDocumentContent'])->getMock();
        $mockSnapVT20->expects($this->any())
            ->method('getDocumentContent')
            ->will($this->returnValue(["TestNumber" => "numberVT20"]));

        $mockSnapVT30 = $this->getMockBuilder('\DvsaDocument\Entity\Document')->disableOriginalConstructor()->setMethods(['getDocumentContent'])->getMock();
        $mockSnapVT30->expects($this->any())
            ->method('getDocumentContent')
            ->will($this->returnValue(["TestNumber" => "numberVT30"]));

        $this->stubPdfRenderer->expects($this->exactly(2))
            ->method('buildPdfParameters')
            ->withConsecutive([$mockSnapVT20], [$mockSnapVT30])
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

    public function testGetPRSW()
    {
        /** @var LambdaReportService|MockObject $service  */
        $service = $this->getMockBuilder(
            LambdaReportService::class)
            ->setConstructorArgs([$this->stubPdfRenderer, $this->client])
            ->onlyMethods(['getReport'])
            ->getMock();

        $this->stubPdfRenderer->expects($this->any())
            ->method('buildPdfParameters')
            ->will($this->returnValue(["TestNumber" => "number"]));

        $mockSnap = $this->getMockBuilder('\DvsaDocument\Entity\Document')->disableOriginalConstructor()->setMethods(['getDocumentContent'])->getMock();
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

    protected function mockFieldValue($key, $value)
    {
        $field = $this->getMockBuilder('\stdClass')->disableOriginalConstructor()->setMethods(['getFieldValue'])->getMock();
        $field->expects($this->once())
            ->method('getFieldValue')
            ->will($this->returnValue($value));

        return [$key, $field];
    }

    protected function getMockReport($resourceName)
    {
        $headerMock = $this->getMockBuilder('\Laminas\Http\Headers')->disableOriginalConstructor()->setMethods(['toString'])->getMock();
        $headerMock->expects($this->any())
            ->method('toString')
            ->will($this->returnValue('Content-Type: 1234'));

        $mock = $this->getMockBuilder(ZendResponse::class)->disableOriginalConstructor()->setMethods(['getHeaders', 'getContent', 'getStatusCode'])->getMock();
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
