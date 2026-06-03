<?php

declare(strict_types=1);

namespace DvsaReportModuleTest\DvsaReport\Service\Pdf;

use DvsaReport\Service\Pdf\PdfRenderer;
use PHPUnit\Framework\TestCase;
use DvsaDocument\Entity\Document as SnapshotDocument;

final class PdfRendererTest extends TestCase
{
    protected PdfRenderer $renderer;

    #[\Override]
    public function setUp(): void
    {
        $this->renderer = new PdfRenderer();
    }

    /**
     * @dataProvider getJasperParameters
     */
    public function testEmptyJasperParams(): void
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
    public function testBuildJasperParameters(array $snapshotData): void
    {
        $snapshot = new SnapshotDocument();
        $snapshot->setDocumentContent($snapshotData);
        $parameterResult = $this->renderer->buildPdfParameters($snapshot);

        $this->assertEquals($parameterResult, $snapshotData);
    }

    public function getJasperParameters(): array
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
