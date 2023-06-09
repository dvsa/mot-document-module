<?php
/**
 * Pdf service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaReport\Service\Pdf;

use Laminas\Config\Config;
use Laminas\Http\Response;

/**
 * Pdf service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PdfService
{

    /**
     * Holds the html
     *
     * @var array
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
     */
    private $tmpDir = '/tmp/';

    /**
     * @var Zend/Config/Config
     */
    private $report_config;

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
     * @return \DvsaDocument\Service\Pdf\PdfService
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get response
     *
     * @return Response;
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
    public function setResponse(Response $response)
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
     * @return \DvsaDocument\Service\Pdf\PdfService
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
     * @return string
     */
    public function generateDocument($fileName)
    {
        $content = $this->generatePdf();

        $response = $this->getResponse();

        $response->setStatusCode(Response::STATUS_CODE_200);

        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/pdf')
            ->addHeaderLine('Content-Disposition', 'inline; filename="' . $fileName . '"')
            ->addHeaderLine('Content-Length', strlen($content));

        $response->setContent($content);

        return $response;
    }

    /**
     * Generate a PDF
     *
     * @return type
     */
    public function generatePdf()
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
    public function generateUsingWkHtmlToPdf()
    {
        // Need to create a tmp html file
        $tmpFilePrefix = realpath($this->getTmpDir()) . '/' . time() . uniqid();

        $binary = $this->getConfig()->get('report_builder')->get('html_to_pdf_binary');

        $tmpHtmlFile = $tmpFilePrefix . '.html';
        $tmpPdfFile = $tmpFilePrefix . '.pdf';

        $command =  $binary . ' -q --disable-internal-links --disable-external-links ' . $tmpHtmlFile . ' ' . $tmpPdfFile . ' 2>&1';

        try {
            file_put_contents($tmpHtmlFile, $this->getHtml());
        } catch (\Exception $ex) {
            throw new \Exception('Failed to create temporary html file');
        }

        $result = shell_exec($command);

        if (!file_exists($tmpPdfFile)) {
            unlink($tmpHtmlFile);
            throw new \Exception("Failed to convert web page to Pdf; wkhtmltopdf result was [$result]");
        }

        $content = file_get_contents($tmpPdfFile);

        unlink($tmpHtmlFile);
        unlink($tmpPdfFile);

        return $content;
    }

    /**
     * Remove unwanted stuff
     *
     * @param string $html
     * @return string
     */
    private function removeUnwantedStuff($html)
    {
        $html = preg_replace('/\<script([^>]+)?\>([^<]+)?\<\/script\>/', '', $html);

        $html = preg_replace('/\<link([^>]+)?rel="shortcut icon"(\ [^>]+)\>/', '', $html);

        $html = preg_replace('/\<link([^>]+)?href="([^"]+)?font([^"]+)?"(\ [^>]+)\>/', '', $html);

        $removals = array('£');

        $html = str_replace($removals, '', $html);

        return $html;
    }

    /**
     * Replace web roots within the html
     *
     * @param string $html
     * @param string $root
     */
    public function replaceWebRoot($html, $root)
    {
        $count = preg_match_all('/(\<[a-zA-Z]+\ ([^>]+)?[src|href]=")(\/[^"]+)("([^>]+)?\>)/', $html, $matches);

        if (!$count) {
            return $html;
        }

        foreach ($matches[0] as $key => $oldTag) {

            $replacement = rtrim($root, '/') . '/' . ltrim($matches[3][$key], '/');

            $newTag = str_replace($matches[3][$key], $replacement, $oldTag);

            $html = str_replace($oldTag, $newTag, $html);
        }

        return $html;
    }

    private function getConfig()
    {
        if (is_null($this->report_config)) {
            $this->report_config = new Config(include __DIR__ . '/../../../../config/report-module.config.php');
        }

        return $this->report_config;
    }
}
