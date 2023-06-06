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
     * @var \DvsaDocument\Entity\Template
     *
     * @ORM\ManyToOne(targetEntity="Template", inversedBy="documents")
     * @ORM\Column(name="template_id", type="integer", nullable=false)
     */
    private $template;

    /**
     * @var string
     *
     * @ORM\Column(name="document_content", type="json_array")
     */
    private $documentContent;

    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getDocumentContent()
    {
        return $this->documentContent;
    }

    /**
     * @param string $documentContent
     *
     * @return $this
     */
    public function setDocumentContent($documentContent)
    {
        if(!empty($documentContent)) {
            $this->documentContent = $documentContent;
        }

        return $this;
    }
}
