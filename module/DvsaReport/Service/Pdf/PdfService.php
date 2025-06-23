<?php

/**
 * Pdf service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaReport\Service\Pdf;

use Exception;
use Laminas\Http\Response;
use Dompdf\Dompdf;
use Dompdf\Options;


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
     *
     * @return string
     * @throws Exception
     */
    public function generatePdf(): string
    {
        $this->setHtml($this->removeUnwantedStuff($this->getHtml()));

        $return = $this->generateUsingDompdf();

        if ($return != null) {
            return $return;
        } else {
            throw new Exception('Failed to generate PDF');
        }
    }

    /**
     * Generate the PDF using dompdf
     *
     * @return string | null
     * @throws \Exception
     */
    public function generateUsingDompdf(): string | null
    {
        $options = new Options();
        $options->set('defaultFont', 'Helvetica'); // Set your desired default font

        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($this->getHtml());

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        return $dompdf->output();
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
}
