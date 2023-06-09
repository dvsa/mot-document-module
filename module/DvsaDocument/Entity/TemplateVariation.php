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
 */
class TemplateVariation extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'TemplateVariation';
    const CLASS_PATH = __CLASS__;

    /**
     * @var \DvsaDocument\Entity\Template
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

    public function setTemplate(\DvsaDocument\Entity\Template $template)
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setJasperReportName($jasperReportName)
    {
        $this->jasperReportName = $jasperReportName;

        return $this;
    }

    public function getJasperReportName()
    {
        return $this->jasperReportName;
    }
}
