<?php

/**
 * Report Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace DvsaReport\Model;

/**
 * Report Model
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class Report
{
    /**
     * Holds the report name (including extension)
     *
     * @var string
     */
    private $name;

    /**
     * Holds the binary data
     *
     * @var string
     */
    private $data;

    /**
     * Holds the mime type
     *
     * @var string
     */
    private $mimeType;

    /**
     * Holds the file size in bytes
     *
     * @var int
     */
    private $size;

    /**
     * Setter for name
     *
     * @param string $name
     * @return \DvsaDocument\Model\Document
     */
    public function setName($name)
    {
        $this->name = str_replace('/', '-', $name);

        return $this;
    }

    /**
     * Getter for name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for data
     *
     * @param string $data
     * @return \DvsaDocument\Model\Document
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Getter for data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Setter for mime type
     *
     * @param string $mimeType
     * @return \DvsaDocument\Model\Document
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Setter for mime type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Setter for size
     *
     * @param int $size
     * @return \DvsaDocument\Model\Document
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Getter for size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }
}
