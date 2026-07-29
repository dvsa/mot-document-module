<?php

declare(strict_types=1);

namespace DvsaReportModuleTest\DvsaReport\Service\Report;

use DvsaDocument\Entity\Document;
use DvsaReport\Service\HttpClient\EnhancedLambdaHttpClientService;
use DvsaReport\Service\Report\LambdaReportService;
use DvsaReport\Model\Report;
use Laminas\Http\Response;
use DvsaReport\Service\Pdf\PdfRenderer;
use DvsaReport\Exceptions\ReportNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** LambdaReportService Test */
final class LambdaReportServiceTest extends TestCase
{
    protected LambdaReportService $service;

    protected EnhancedLambdaHttpClientService&MockObject $client;

    protected PdfRenderer&MockObject $stubPdfRenderer;

    #[\Override]
    public function setUp(): void
    {
        $this->stubPdfRenderer = $this->getMockBuilder(PdfRenderer::class)->disableOriginalConstructor()->onlyMethods(['buildPdfParameters'])->getMock();

        $this->client = $this->getMockBuilder(EnhancedLambdaHttpClientService::class)->disableOriginalConstructor()->getMock();

        $this->service = new LambdaReportService($this->stubPdfRenderer, $this->client);
    }

    public function testGetHttpClient(): void
    {
        $this->assertSame($this->client, $this->service->getHttpClient());
    }

    public function testGetDocumentThrowsExpectedExceptionWithFailedResponse(): void
    {
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

    public function testGetDocumentWhenSuccessful(): void
    {
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

    public function testGetVt20W(): void
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

    public function testGetVt30W(): void
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

    public function testGetPRS(): void
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

    public function testGetPRSW(): void
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

    protected function mockFieldValue(string $key, mixed $value): array
    {
        $field = $this->getMockBuilder(\stdClass::class)->disableOriginalConstructor()->addMethods(['getFieldValue'])->getMock();
        $field->expects($this->any())
            ->method('getFieldValue')
            ->will($this->returnValue($value));

        return [$key, $field];
    }
}
