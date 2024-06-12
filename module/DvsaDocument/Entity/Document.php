<?php

namespace DvsaDocument\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DvsaDocument\EntityTrait\CommonIdentityTrait;

/**
 * Document Entity
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @ORM\Table(name="jasper_document")
 * @ORM\Entity
 */
class Document extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Template", inversedBy="documents")
     * @ORM\Column(name="template_id", type="integer", nullable=false)
     */
    private $template;

    /**
     * @var string|array
     *
     * @ORM\Column(name="document_content", type="json_array")
     */
    private $documentContent;

    /**
     * @param integer $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return integer
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return string|array
     */
    public function getDocumentContent()
    {
        return $this->documentContent;
    }

    /**
     * @param array|string|null $documentContent
     *
     * @return $this
     */
    public function setDocumentContent($documentContent)
    {
        if (null === $documentContent) {
            return $this;
        }

        if (!empty($documentContent)) {
            $this->documentContent = $documentContent;
        }

        return $this;
    }
}
