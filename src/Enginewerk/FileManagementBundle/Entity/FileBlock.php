<?php
namespace Enginewerk\FileManagementBundle\Entity;

/**
 * FileBlock
 */
class FileBlock
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $fileHash;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $rangeStart;

    /**
     * @var int
     */
    private $rangeEnd;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \Enginewerk\FileManagementBundle\Entity\File
     */
    private $file;

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
     * Set fileHash
     *
     * @param string $fileHash
     *
     * @return FileBlock
     */
    public function setFileHash($fileHash)
    {
        $this->fileHash = $fileHash;

        return $this;
    }

    /**
     * Get fileHash
     *
     * @return string
     */
    public function getFileHash()
    {
        return $this->fileHash;
    }

    /**
     * Set size
     *
     * @param int $size
     *
     * @return FileBlock
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

    /**
     * Set rangeStart
     *
     * @param int $rangeStart
     *
     * @return FileBlock
     */
    public function setRangeStart($rangeStart)
    {
        $this->rangeStart = $rangeStart;

        return $this;
    }

    /**
     * Get rangeStart
     *
     * @return int
     */
    public function getRangeStart()
    {
        return $this->rangeStart;
    }

    /**
     * Set rangeEnd
     *
     * @param int $rangeEnd
     *
     * @return FileBlock
     */
    public function setRangeEnd($rangeEnd)
    {
        $this->rangeEnd = $rangeEnd;

        return $this;
    }

    /**
     * Get rangeEnd
     *
     * @return int
     */
    public function getRangeEnd()
    {
        return $this->rangeEnd;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FileBlock
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return FileBlock
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set file
     *
     * @param \Enginewerk\FileManagementBundle\Entity\File $file
     *
     * @return FileBlock
     */
    public function setFile(\Enginewerk\FileManagementBundle\Entity\File $file = null)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return \Enginewerk\FileManagementBundle\Entity\File
     */
    public function getFile()
    {
        return $this->file;
    }
}
