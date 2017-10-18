<?php
namespace Enginewerk\EmissionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Enginewerk\UserBundle\Entity\User;

class File
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $publicIdentifier;

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
     * @var ArrayCollection
     */
    private $fileBlocks;

    /**
     * @var User
     */
    private $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fileBlocks = new ArrayCollection();
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
     * Set publicIdentifier
     *
     * @param string $publicIdentifier
     *
     * @return File
     */
    public function setPublicIdentifier($publicIdentifier)
    {
        $this->publicIdentifier = $publicIdentifier;

        return $this;
    }

    /**
     * Get publicIdentifier
     *
     * @return string
     */
    public function getPublicIdentifier()
    {
        return $this->publicIdentifier;
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
     * @param FileBlock $fileBlock
     *
     * @return File
     */
    public function addFileBlock(FileBlock $fileBlock)
    {
        $this->fileBlocks[] = $fileBlock;

        return $this;
    }

    /**
     * Remove fileBlock
     *
     * @param FileBlock $fileBlock
     */
    public function removeFileBlock(FileBlock $fileBlock)
    {
        $this->fileBlocks->removeElement($fileBlock);
    }

    /**
     * Get fileBlocks
     *
     * @return ArrayCollection
     */
    public function getFileBlocks()
    {
        return $this->fileBlocks;
    }

    /**
     * @param User $user
     *
     * @return File
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function preChange()
    {
        $currentTime = new \DateTime();
        $currentTime->setTimestamp(time());

        if (null === $this->getCreatedAt()) {
            $this->setCreatedAt($currentTime);
        }

        $this->setUpdatedAt($currentTime);
    }
}
