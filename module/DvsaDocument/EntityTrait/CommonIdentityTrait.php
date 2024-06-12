<?php

/**
 * Identity Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace DvsaDocument\EntityTrait;

/**
 * CommonIdentityTrait
 */
trait CommonIdentityTrait
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @param mixed $id
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setId($id)
    {
        if (null != $id && !is_numeric($id)) {
            /** @var string $id */
            throw new \InvalidArgumentException("Expected numeric id, got [$id]");
        }
        $this->id = (int)$id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
