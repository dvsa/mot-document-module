<?php

/**
 * Pdf service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaReport\Service\Pdf;

use Laminas\Http\Response;

/**
 * Pdf service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @psalm-suppress ClassMustBeFinal cannot be final or tests would need overhall
 */
class PdfService
{
    /**
     * Holds the html
     *
     * @var string
     */
    private $html;

    /**
     * Holds the response
     *
     * @var Response
     */
    private $response;

    /**
     * Holds the tmp directory
     *
     * @var string
     */
    private $tmpDir = '/tmp/';

    /**
     * Holds the report config, and acts as cache so only fetched once from
     * the getConfig method
     */
    private ?array $report_config = null;

    /**
     * Get html
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set HTML
     *
     * @param string $html
     *
     * @return $this
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set response
     *
     * @param Response $response
     */
    public function setResponse($response): void
    {
        $this->response = $response;
    }

    /**
     * Get tmpDir
     *
     * @return string
     */
    public function getTmpDir()
    {
        return $this->tmpDir;
    }

    /**
     * Set tmpDir
     *
     * @param string $tmpDir
     *
     * @return $this
     */
    public function setTmpDir($tmpDir)
    {
        $this->tmpDir = $tmpDir;

        return $this;
    }

    /**
     * Generate the Pdf content
     *
     * @param string $fileName
     * @return Response
     */
    public function generateDocument($fileName)
    {
        $content = $this->generatePdf();

        $response = $this->getResponse();

        $response->setStatusCode(Response::STATUS_CODE_200);

        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/pdf')
            ->addHeaderLine('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->addHeaderLine('Content-Length', strval(strlen($content)));

        $response->setContent($content);

        return $response;
    }

    /**
     * Generate a PDF
     */
    public function generatePdf(): string
    {
        $this->setHtml($this->removeUnwantedStuff($this->getHtml()));

        return $this->generateUsingWkHtmlToPdf();
    }

    /**
     * Generate the PDF using WkHtmlToPdf
     *
     * @return string
     * @throws \Exception
     */
    public function generateUsingWkHtmlToPdf(): string
    {
        // Need to create a tmp html file
        /** @psalm-suppress PossiblyFalseOperand
         *
         * Reasons for false
         * The directory doesn't exist
         * The path is invalid
         * There are permission issues
         * The path contains symbolic links that can't be resolved
         *
         * Ignore since none of the above can happen and if they do they should happen, in dev 1st through testing
         * plus the fix would be throw an error which will already happen
         * */
        $tmpFilePrefix = realpath($this->getTmpDir()) . '/' . time() . uniqid();

        $config = $this->getConfig();
        if (!isset($config['report_builder']) || !is_array($config['report_builder'])) {
            throw new \Exception('Invalid configuration: report_builder not found');
        }

        $reportBuilderConfig = $config['report_builder'];
        if (!isset($reportBuilderConfig['html_to_pdf_binary']) || !is_string($reportBuilderConfig['html_to_pdf_binary'])) {
            throw new \Exception('Invalid configuration: html_to_pdf_binary not found');
        }

        $binary = $reportBuilderConfig['html_to_pdf_binary'];

        $tmpHtmlFile = $tmpFilePrefix . '.html';
        $tmpPdfFile = $tmpFilePrefix . '.pdf';

        $command =  $binary . ' -q --disable-internal-links --disable-external-links ' . $tmpHtmlFile . ' ' . $tmpPdfFile . ' 2>&1';

        try {
            file_put_contents($tmpHtmlFile, $this->getHtml());
        } catch (\Exception $ex) {
            throw new \Exception('Failed to create temporary html file');
        }

        /** @psalm-suppress ForbiddenCode */
        $result = shell_exec($command);

        if (!file_exists($tmpPdfFile)) {
            unlink($tmpHtmlFile);
            throw new \Exception("Failed to convert web page to Pdf; wkhtmltopdf result was [$result]");
        }

        $content = file_get_contents($tmpPdfFile);

        unlink($tmpHtmlFile);
        unlink($tmpPdfFile);

        if (false === $content) {
            throw new \Exception("Failed to read converted pdf");
        }

        return $content;
    }

    /**
     * Remove unwanted stuff
     *
     * @param string|null $html
     * @return string
     */
    private function removeUnwantedStuff($html)
    {
        $html = preg_replace('/\<script([^>]+)?\>([^<]+)?\<\/script\>/', '', $html ?? '');

        $html = preg_replace('/\<link([^>]+)?rel="shortcut icon"(\ [^>]+)\>/', '', $html ?? '');

        $html = preg_replace('/\<link([^>]+)?href="([^"]+)?font([^"]+)?"(\ [^>]+)\>/', '', $html ?? '');

        $removals = array('£');

        $html = str_replace($removals, '', $html ?? '');

        return $html;
    }

    /**
     * Replace web roots within the html
     *
     * @param string|null $html
     * @param string $root
     *
     * @return string
     */
    public function replaceWebRoot($html, $root)
    {
        $html = $html ?? '';
        $count = preg_match_all('/(\<[a-zA-Z]+\ ([^>]+)?[src|href]=")(\/[^"]+)("([^>]+)?\>)/', $html, $matches);

        if (false === $count) {
            return $html;
        }

        foreach ($matches[0] as $key => $oldTag) {
            $replacement = rtrim($root, '/') . '/' . ltrim($matches[3][$key], '/');

            $newTag = str_replace($matches[3][$key], $replacement, $oldTag);

            $html = str_replace($oldTag, $newTag, $html);
        }

        return $html;
    }

    /**
     * @return array
     */
    private function getConfig(): array
    {
        if (is_null($this->report_config)) {
            /** @var array $config */
            $config = include __DIR__ . '/../../../../config/report-module.config.php';
            $this->report_config = $config;
        }
        return $this->report_config;
    }
}
