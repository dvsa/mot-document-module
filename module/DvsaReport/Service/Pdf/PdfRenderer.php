<?php

namespace DvsaReport\Service\Pdf;

use DvsaDocument\Entity\Document;

/**
 * Jasper PDF report generation
 * @psalm-suppress ClassMustBeFinal cannot be final or tests would need overhall
 */
class PdfRenderer
{
    /**
     * @param Document $snapshotData
     *
     * @return array
     */
    public function buildPdfParameters(Document $snapshotData)
    {
        $assembled = [];
        /** @var array $parameters */
        $parameters = $snapshotData->getDocumentContent();

        if (empty($parameters)) {
            return $assembled;
        }

        foreach ($parameters as $name => $value) {
            $assembled[$name] = $this->buildParameterValue($value);
        }

        return $assembled;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
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
