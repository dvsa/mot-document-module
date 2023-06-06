<?php

/**
 * TemplateType Entity
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace DvsaDocument\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaDocument\EntityTrait\CommonIdentityTrait;

/**
 * TemplateType Entity
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @ORM\Table(name="jasper_template_type")
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class TemplateType extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'TemplateType';
    const CLASS_PATH = __CLASS__;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
}
