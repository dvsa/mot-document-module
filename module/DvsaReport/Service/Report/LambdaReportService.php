<?php

namespace DvsaReport\Service\Report;

use DvsaReport\Exceptions\ReportNotFoundException;
use DvsaReport\Model\Report;
use DvsaReport\Service\Encoder\ParamsEncoder;
use DvsaReport\Service\HttpClient\HttpClientServiceInterface;
use DvsaReport\Service\Pdf\PdfRenderer;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Http\Header\HeaderInterface;
use DvsaReport\Model\ReportNames;

class LambdaReportService
{
    public const CONTENT_TYPE_JSON = 'application/json; charset=utf-8';
    public const CONTENT_TYPE_PDF = 'application/pdf';
    public const HTTP_ATTR_ID = 'ID';
    public const HTTP_ATTR_SNAPSHOT = 'DATA';
    public const HTTP_ATTR_FAIL_SNAPSHOT = 'FAIL_DATA';
    public const REPORTS_FOR_FAIL_SNAPSHOT_DATA = [
        ReportNames::VT30, ReportNames::VT30W, ReportNames::VT32VE, ReportNames::VT32VEW, ReportNames::EU_VT32VE,
        ReportNames::EU_VT32VEW
    ];

    /**
     * Holds the module's HTTP Client
     * @var HttpClientServiceInterface
     */
    protected $httpClient;

    /**
     * This is responsible for encapsulating what's involved in handling the PDF rendering process.
     * As it stands there's a lot of this logic inside this ReportService class, and the intention is to
     * gradually move it to PdfRenderer, over time.
     *
     * @var PdfRenderer
     */
    protected $pdfRenderer;

    /** @var ParamsEncoder */
    private $encoder;

    /**
     * @param PdfRenderer $pdfRenderer
     * @param HttpClientServiceInterface $httpClient
     */
    public function __construct(PdfRenderer $pdfRenderer, HttpClientServiceInterface $httpClient)
    {
        $this->pdfRenderer = $pdfRenderer;
        $this->httpClient = $httpClient;
        $this->encoder = new ParamsEncoder();
    }

    /**
     * Issue a request to the Lambda API to render a given report with ad-hoc
     * parameters; not necessarily bound to a document (and by extension, DB query)
     *
     * @param string $reportName
     * @param array $params
     *
     * @return Response
     */
    public function getReport($reportName, $params = [])
    {
        $request = $this->httpClient->getRequest();

        $uri = sprintf('%s/%s', $this->httpClient->getDomainUrl(), $reportName);
        $json_params = $this->encoder->arrayToJson($params);

        $request->setMethod(Request::METHOD_POST);
        $this->httpClient->setRequest($request);
        $this->httpClient->setContent($json_params);
        $this->httpClient->setUri($uri);

        $report = $this->httpClient->dispatch();

        return $this->generatePdfContent($report);
    }

    /**
     * @param array $runtimeParams
     */
    private function setupSnapshotData(&$runtimeParams = []): void
    {
        $this->convertSnapshotDataToJson('snapshotData', self::HTTP_ATTR_SNAPSHOT, $runtimeParams);
        $this->convertSnapshotDataToJson('snapshotFailData', self::HTTP_ATTR_FAIL_SNAPSHOT, $runtimeParams);
    }

    /**
     * @param string $sourceSnapshotDataKey
     * @param string $destSnapshotDataKey
     * @param array $runtimeParams
     */
    private function convertSnapshotDataToJson($sourceSnapshotDataKey, $destSnapshotDataKey, &$runtimeParams = []): void
    {
        if (isset($runtimeParams[$sourceSnapshotDataKey])) {
            $runtimeParams[$destSnapshotDataKey] = json_encode($this->pdfRenderer->buildPdfParameters(
                $runtimeParams[$sourceSnapshotDataKey]
            ));
            // We no longer need 'snapshotData' as it's been transformed into $runtimeParams[HTTP_ATTR_SNAPSHOT]
            unset($runtimeParams[$sourceSnapshotDataKey]);
        }
    }

    private function generatePdfContent(Response $response): Response
    {
        /** @var string */
        $responseContent = $response->getContent();
        $content = base64_decode($responseContent);

        $response = new Response();

        $headers = $response->getHeaders();
        $headers->addHeaderLine("Content-Type", self::CONTENT_TYPE_PDF);
        $headers->addHeaderLine("Content-Length", strval(strlen($content)));
        $response->setHeaders($headers);

        $response->setContent($content);

        return $response;
    }

    /**
     * Issue a request to the Lambda API to render a given document's
     * contents into a jasper report
     *
     * @param int    $documentId
     * @param string $reportName
     * @param array  $runtimeParams
     *
     * @return Response
     */
    public function getReportById($documentId, $reportName, $runtimeParams = [])
    {
        $runtimeParams[self::HTTP_ATTR_ID] = $documentId;

        if (in_array($reportName, self::REPORTS_FOR_FAIL_SNAPSHOT_DATA)) {
            $runtimeParams["snapshotFailData"] = $runtimeParams["snapshotData"];
            $this->convertSnapshotDataToJson('snapshotFailData', self::HTTP_ATTR_FAIL_SNAPSHOT, $runtimeParams);
            unset($runtimeParams['snapshotData']);
        } else {
            $this->convertSnapshotDataToJson('snapshotData', self::HTTP_ATTR_SNAPSHOT, $runtimeParams);
        }

        return $this->getReport($reportName, $runtimeParams);
    }


    /**
     * Wrapper function to retrieve individual reports all merged into a single PDF
     *
     * @param bool   $isPrs
     * @param array  $argList
     *
     * @return Response
     */
    public function getMergedPdfReports($isPrs, $argList = [])
    {
        $reportName = $this->resolveReportName($isPrs, $argList);

        $runtimeParams = $argList[0]["runtimeParams"];
        foreach ($argList as $argument) {
            if (in_array($argument['reportName'], self::REPORTS_FOR_FAIL_SNAPSHOT_DATA)) {
                $runtimeParams["snapshotFailData"] = $argument["runtimeParams"]["snapshotData"];

                if (count($argList) === 1) {
                    unset($runtimeParams["snapshotData"]);
                }
            } else {
                $runtimeParams["snapshotData"] = $argument["runtimeParams"]["snapshotData"];
            }
        }

        $this->setupSnapshotData($runtimeParams);

        return $this->getReport($reportName, $runtimeParams);
    }

    /**
     * @param bool $isPrs
     * @param array $reports
     *
     * @return string
     */
    private function resolveReportName($isPrs, $reports = [])
    {
        return $isPrs ? $this->resolvePrsReportName($reports) : $this->resolveNonPrsReportName($reports);
    }

    /**
     * @param array $reports
     *
     * @return string
     */
    private function resolvePrsReportName($reports)
    {
        $reportName = ReportNames::PRS;

        foreach ($reports as $report) {
            if ($report["reportName"] === ReportNames::VT20W) {
                $reportName = ReportNames::PRSW;
            }
        }

        return $reportName;
    }

    /**
     * @param array $reports
     *
     * @return string
     */
    private function resolveNonPrsReportName($reports)
    {
        $reportName = "";
        if (count($reports) === 1) {
            $reportName = $reports[0]["reportName"];
        } else {
            foreach ($reports as $report) {
                if ($report["reportName"] === ReportNames::VT20W) {
                    $reportName = ReportNames::VT20W;
                } elseif ($report["reportName"] === ReportNames::VT30W) {
                    $reportName = ReportNames::VT30W;
                }
            }
        }

        return $reportName;
    }


    /**
     * Transform a previously returned response into a friendly Report model
     *
     * @param Response $response
     */
    public function getReportFromResponse(Response $response): Report
    {
        if (!$response->isSuccess()) {
            throw new ReportNotFoundException($response->getReasonPhrase());
        }

        $headers = $response->getHeaders();

        /** @var HeaderInterface */
        $contentType = $headers->get('Content-Type');

        /** @var HeaderInterface */
        $contentLength = $headers->get('Content-Length');

        $report = new Report();

        $report->setData($response->getBody());

        $report->setMimeType(
            $contentType->getFieldValue()
        );
        $report->setSize(
            intval($contentLength->getFieldValue())
        );

        return $report;
    }

    /**
     * @return HttpClientServiceInterface
     */
    public function getHttpClient(): HttpClientServiceInterface
    {
        return $this->httpClient;
    }

    /**
     * @param HttpClientServiceInterface $httpClient
     */
    public function setHttpClient(HttpClientServiceInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }
}
