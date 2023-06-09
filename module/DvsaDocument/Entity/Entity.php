<?php

namespace DvsaDocument\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * An abstract entity for every entity existing in MOT schema
 *
 * @ORM\MappedSuperclass
 */
abstract class Entity
{
    /**
     * @var int
     *
     * @ORM\Column(name="created_by", type="integer", nullable=false)
     */
    protected $createdBy;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="created_on", type="datetime", nullable=false)
     */
    protected $createdOn;

    /**
     * @var int
     *
     * @ORM\Column(name="last_updated_by", type="integer", nullable=false)
     */
    protected $lastUpdatedBy;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="last_updated_on", type="datetime", nullable=true)
     */
    protected $lastUpdatedOn;

    /**
     * @var integer
     *
     * @ORM\Version @ORM\Column(type="integer", nullable=false)
     */
    protected $version = 1;

    //region getters and setters

    /**
     * @return integer|null
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setCreatedBy($value)
    {
        $this->createdBy = $value;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setCreatedOn(\DateTime $date)
    {
        $this->createdOn = $date;

        return $this;
    }

    /**
     * @return integer|null
     */
    public function getLastUpdatedBy()
    {
        return $this->lastUpdatedBy;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setLastUpdatedBy($value)
    {
        $this->lastUpdatedBy = $value;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdatedOn()
    {
        return $this->lastUpdatedOn;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setLastUpdatedOn(\DateTime $date)
    {
        $this->lastUpdatedOn = $date;

        return $this;
    }

    /**
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param integer $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }
    //endregion

    /**
     * Return true if entity was last modified or just created by user of id
     *
     * @param integer $userId
     *
     * @return boolean
     */
    public function isLastModifiedBy($userId)
    {
        $modifiedBy = $this->lastUpdatedBy ? $this->lastUpdatedBy : $this->createdBy;

        return $modifiedBy === null || $userId === $modifiedBy;
    }
}
