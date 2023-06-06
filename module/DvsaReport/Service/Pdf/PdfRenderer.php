<?php

namespace DvsaReport\Service\Pdf;

use DvsaDocument\Entity\Document;

/**
 * Jasper PDF report generation
 */
class PdfRenderer
{

    /**
     * @param DvsaDocument\Entity\Document $snapshotData
     *
     * @return array
     */
    public function buildPdfParameters(Document $snapshotData)
    {
        $assembled = [];
        $parameters = $snapshotData->getDocumentContent();

        if(empty($parameters)) {
            return $assembled;
        }

        foreach ($parameters as $name => $value) {
            $assembled[$name] = $this->buildParameterValue($value);
        }

        return $assembled;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function buildParameterValue($value)
    {
        if (null === $value) {
            return '';
        }

        if (!is_scalar($value) && !is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }
}