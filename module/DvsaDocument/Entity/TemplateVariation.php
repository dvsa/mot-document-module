<?php

/**
 * TemplateVariation Entity
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocument\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaDocument\EntityTrait\CommonIdentityTrait;

/**
 * TemplateVariation Entity
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @ORM\Table(name="jasper_template_variation")
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 * @psalm-suppress ClassMustBeFinal Intentionally extensible in downstream repos.
 */
class TemplateVariation extends Entity
{
    use CommonIdentityTrait;

    public const ENTITY_NAME = 'TemplateVariation';
    public const CLASS_PATH = __CLASS__;

    /**
     * @var Template
     *
     * @ORM\ManyToOne(targetEntity="Template", inversedBy="variations")
     * @ORM\Column(name="template_id", type="integer", nullable=false)
     */
    private $template;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="jasper_report_name", type="string", nullable=false)
     */
    private $jasperReportName;

    /**
     * @return $this
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function setTemplate(Template $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return Template
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return $this
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     * @psalm-suppress PossiblyUnusedMethod BL-22047
     */
    public function getName()
    {
        return $this->name;
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
}
