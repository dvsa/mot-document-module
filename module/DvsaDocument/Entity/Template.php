<?php

/**
 * Template Entity
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocument\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use DvsaDocument\EntityTrait\CommonIdentityTrait;

/**
 * Template Entity
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @ORM\Table(name="jasper_template")
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class Template extends Entity
{
    use CommonIdentityTrait;

    public const ENTITY_NAME = 'Template';
    public const CLASS_PATH = __CLASS__;

    /**
     * @var \DvsaDocument\Entity\TemplateType
     *
     * @ORM\OneToMany(targetEntity="TemplateType", mappedBy="Templates")
     * @ORM\Column(name="template_type_id", type="integer", nullable=false)
     */
    private $templateType;

    /**
     * @var string
     *
     * @ORM\Column(name="jasper_report_name", type="string", nullable=false)
     */
    private $jasperReportName;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Document", inversedBy="template")
     */
    private $documents;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="TemplateVariation", mappedBy="template")
     */
    private $variations;

    /**
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    /**
     * @return $this
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function setTemplateType(\DvsaDocument\Entity\TemplateType $templateType)
    {
        $this->templateType = $templateType;

        return $this;
    }

    /**
     * @return TemplateType
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function getTemplateType()
    {
        return $this->templateType;
    }

    /**
     * @return $this
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function setJasperReportName(string $jasperReportName)
    {
        $this->jasperReportName = $jasperReportName;

        return $this;
    }

    /**
     * @return string
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function getJasperReportName()
    {
        return $this->jasperReportName;
    }

    /**
     * @return $this
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return bool
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @return $this
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function setDocuments(\Doctrine\Common\Collections\ArrayCollection $documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * @return ArrayCollection
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @return $this
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function setVariations(\Doctrine\Common\Collections\ArrayCollection $variations)
    {
        $this->variations = $variations;

        return $this;
    }

    /**
     * @return ArrayCollection
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function getVariations()
    {
        return $this->variations;
    }
}
