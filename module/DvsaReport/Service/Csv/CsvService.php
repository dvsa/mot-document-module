<?php

/**
 * Csv service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaReport\Service\Csv;

use Laminas\Http\Response;

/**
 * Csv service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CsvService
{
    /**
     * Holds the data rows
     *
     * @var array
     */
    private $data = array();

    /**
     * Holds the response
     *
     * @var Response
     */
    private $response;

    /**
     * Holds the content
     *
     * @var string
     */
    private $content;

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data = array())
    {
        $this->data = $data;

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
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }


    /**
     * Generate the CSV content
     *
     * @param string $fileName
     *
     * @return Response
     */
    public function generateDocument($fileName)
    {
        $content = $this->getContent();

        $response = $this->getResponse();

        $response->setStatusCode(Response::STATUS_CODE_200);

        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'text/csv')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->addHeaderLine('Content-Length', strval(strlen($content)));

        $response->setContent($content);

        return $response;
    }

    /**
     * Get the content
     *
     * @return string
     */
    private function getContent()
    {
        if (empty($this->content)) {
            $rows = array();

            $first = true;

            foreach ($this->getData() as $row) {
                $row = $this->cleanRow($row);

                if ($first == true) {
                    $first = false;

                    $rows[] = '"' . implode('","', array_keys($row)) . '"';
                }

                $rows[] = '"' . implode('","', array_values($row)) . '"';
            }

            $this->content = implode("\n", $rows);
        }

        return $this->content;
    }

    /**
     * Remove unwanted
     *
     * @param array $row
     * @return array
     */
    private function cleanRow($row)
    {
        $removals = array('£');

        foreach ($row as $key => $value) {
            $row[$key] = str_replace($removals, '', strip_tags($value));
        }

        return $row;
    }
}
