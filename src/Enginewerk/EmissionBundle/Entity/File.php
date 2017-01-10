<?php
namespace Enginewerk\EmissionBundle\Entity;

use Enginewerk\EmissionBundle\Generator\Hash;

/**
 * File
 */
class File
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $fileId;

    /**
     * @var string
     */
    private $fileHash;

    /**
     * @var string
     */
    private $checksum;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $size;

    /**
     * @var \DateTime
     */
    private $expirationDate;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var bool
     */
    private $complete;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $fileBlocks;

    /**
     * @var \Enginewerk\UserBundle\Entity\User
     */
    private $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fileBlocks = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set fileId
     *
     * @param string $fileId
     *
     * @return File
     */
    public function setFileId($fileId)
    {
        $this->fileId = $fileId;

        return $this;
    }

    /**
     * Get fileId
     *
     * @return string
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * Set fileHash
     *
     * @param string $fileHash
     *
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
     * Set checksum
     *
     * @param string $checksum
     *
     * @return File
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
     * Set name
     *
     * @param string $name
     *
     * @return File
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return File
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set size
     *
     * @param int $size
     *
     * @return File
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
     * Set expirationDate
     *
     * @param \DateTime $expirationDate
     *
     * @return File
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Get expirationDate
     *
     * @return \DateTime
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return File
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
     * @return File
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
     * Set complete
     *
     * @param bool $complete
     *
     * @return File
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;

        return $this;
    }

    /**
     * Get complete
     *
     * @return bool
     */
    public function getComplete()
    {
        return $this->complete;
    }

    /**
     * Add fileBlock
     *
     * @param \Enginewerk\EmissionBundle\Entity\FileBlock $fileBlock
     *
     * @return File
     */
    public function addFileBlock(\Enginewerk\EmissionBundle\Entity\FileBlock $fileBlock)
    {
        $this->fileBlocks[] = $fileBlock;

        return $this;
    }

    /**
     * Remove fileBlock
     *
     * @param \Enginewerk\EmissionBundle\Entity\FileBlock $fileBlock
     */
    public function removeFileBlock(\Enginewerk\EmissionBundle\Entity\FileBlock $fileBlock)
    {
        $this->fileBlocks->removeElement($fileBlock);
    }

    /**
     * Get fileBlocks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFileBlocks()
    {
        return $this->fileBlocks;
    }

    /**
     * Set user
     *
     * @param \Enginewerk\UserBundle\Entity\User $user
     *
     * @return File
     */
    public function setUser(\Enginewerk\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Enginewerk\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function preChange()
    {
        $currentTime = new \DateTime();
        $currentTime->setTimestamp(time());

        if (null === $this->getFileHash()) {
            $fileHash = sha1(uniqid(mt_rand(), true));
            $this->fileHash = $fileHash;
            $this->setCreatedAt($currentTime);
            $this->setFileId(Hash::genereateRandomHash(8));

            $expirationTime = new \DateTime();
            $expirationTime->setTimestamp(time() + 86600);
            $this->setExpirationDate($expirationTime);
        }

        $this->setUpdatedAt($currentTime);
    }
}
