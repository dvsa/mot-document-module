<?php

namespace DvsaReportModuleTest\DvsaReport\Service\Pdf;

use DvsaReport\Service\Pdf\PdfRenderer;
use PHPUnit\Framework\TestCase;
use DvsaDocument\Entity\Document as SnapshotDocument;

class PdfRendererTest extends TestCase
{

    /** @var  PdfRenderer */
    protected $renderer;

    public function setUp(): void
    {
        $this->renderer = new PdfRenderer();
    }

    /**
     * @dataProvider getJasperParameters
     */
    public function testEmptyJasperParams($snapshotData)
    {
        $snapshot = new SnapshotDocument();
        $snapshot->setDocumentContent(null);
        $parameterResult = $this->renderer->buildPdfParameters($snapshot);
        $this->assertEquals([], $parameterResult);
    }

    /**
     * Test generate document
     *
     * @dataProvider getJasperParameters
     */
    public function testBuildJasperParameters($snapshotData)
    {
        $snapshot = new SnapshotDocument();
        $snapshot->setDocumentContent($snapshotData);
        $parameterResult = $this->renderer->buildPdfParameters($snapshot);

        $this->assertEquals($parameterResult, $snapshotData);

    }

    /**
     * @return array
     */
    public function getJasperParameters()
    {
        return [
            [
                [
                    'TestNumber' => '366709905212',
                    'VRM' => 'UK045',
                    'VIN' => '1M7GDM9AXKP042715'
                ]
            ]
        ];
    }
}
