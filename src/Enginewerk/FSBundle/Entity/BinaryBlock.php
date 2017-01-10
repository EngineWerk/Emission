<?php
namespace Enginewerk\FSBundle\Entity;

class BinaryBlock
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $urn;

    /**
     * @var string
     */
    private $checksum;

    /**
     * @var int
     */
    private $size;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set urn
     *
     * @param string $urn
     *
     * @return BinaryBlock
     */
    public function setUrn($urn)
    {
        $this->urn = $urn;

        return $this;
    }

    /**
     * Get urn
     *
     * @return string
     */
    public function getUrn()
    {
        return $this->urn;
    }

    /**
     * Set checksum
     *
     * @param string $checksum
     *
     * @return BinaryBlock
     */
    public function setChecksum($checksum)
    {
        $this->checksum = $checksum;

        return $this;
    }

    /**
     * Get checksum
     *
     * @return string
     */
    public function getChecksum()
    {
        return $this->checksum;
    }

    /**
     * Set size
     *
     * @param int $size
     *
     * @return BinaryBlock
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }
}
