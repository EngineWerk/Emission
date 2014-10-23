<?php

namespace Enginewerk\EmissionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FileBlock
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="file_block")
 * @ORM\Entity(repositoryClass="Enginewerk\EmissionBundle\Entity\FileBlockRepository")
 */
class FileBlock
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    protected $id;

    /**
     * Name for storage file name.
     * Lower characters, and numbers [a-z0-9].
     *
     * @ORM\Column(type="string", length=41)
     * @var string
     */
    protected $fileHash;

    /**
     * @ORM\Column(name="size", type="integer", options={"unsigned"=true})
     * @Assert\Type(type="numeric")
     */
    protected $size;

    /**
     * First byte position
     * @ORM\Column(name="rangeStart", type="bigint", options={"unsigned"=true})
     * @Assert\Type(type="numeric")
     */
    protected $rangeStart;

    /**
     * Last byte position
     * Always greater than 0
     *
     * @ORM\Column(name="rangeEnd", type="bigint", options={"unsigned"=true})
     * @Assert\Type(type="numeric")
     */
    protected $rangeEnd;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="File", inversedBy="fileBlocks", cascade={"persist", "refresh"})
     * @ORM\JoinColumn(name="fileId", referencedColumnName="id", onDelete="cascade")
     */
    protected $file;

    /**
     * @ORM\Column(name="fileId", type="integer", options={"unsigned"=true})
     * @var integer
     */
    protected $fileId;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fileHash.
     *
     *
     * @param  string $fileHash
     * @return File
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
     * @param  integer   $size
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
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set createdAt
     *
     * @param  \DateTime $createdAt
     * @return FileBlock
     */
    public function setCreatedAt(\DateTime $createdAt)
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
     * @param  \DateTime $updatedAt
     * @return FileBlock
     */
    public function setUpdatedAt(\DateTime $updatedAt)
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
     * Set rangeStart
     *
     * @param  integer   $rangeStart
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
     * @return integer
     */
    public function getRangeStart()
    {
        return $this->rangeStart;
    }

    /**
     * Set rangeEnd
     *
     * @param  integer   $rangeEnd
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
     * @return integer
     */
    public function getRangeEnd()
    {
        return $this->rangeEnd;
    }

    /**
     * Set file
     *
     * @param  \Enginewerk\EmissionBundle\Entity\File $file
     * @return \Enginewerk\EmissionBundle\Entity\File
     */
    public function setFile(\Enginewerk\EmissionBundle\Entity\File $file = null)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return \Enginewerk\EmissionBundle\Entity\File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set fileId
     *
     * @param  integer   $fileId
     * @return FileBlock
     */
    public function setFileId($fileId)
    {
        $this->fileId = $fileId;

        return $this;
    }

    /**
     * Get fileId
     *
     * @return integer
     */
    public function getFileId()
    {
        return $this->fileId;
    }
}
