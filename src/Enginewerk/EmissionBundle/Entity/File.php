<?php

namespace Enginewerk\EmissionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Enginewerk\UserBundle\Entity\User;
use Enginewerk\EmissionBundle\Generator\Hash;

/**
 * File
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="file")
 * @ORM\Entity(repositoryClass="Enginewerk\EmissionBundle\Entity\FileRepository")
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    protected $id;

    /**
     * File Identification (public download name)
     * Shortest possible name, for public identicifation [a-zA-Z0-9]
     * #BUG DB must be case sensitive
     *
     * @ORM\Column(type="string", length=16)
     * @var string
     */
    protected $fileId;

    /**
     * Name for storage file name.
     * Lower characters, and numbers [a-z0-9].
     *
     * @ORM\Column(type="string", length=41)
     * @Assert\Length(min="41", max="41")
     * @var string
     */
    protected $fileHash;

    /**
     * File owner
     *
     * @ORM\ManyToOne(targetEntity="\Enginewerk\UserBundle\Entity\User", inversedBy="files", cascade={"persist", "refresh"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $user;

    /**
     * Checksum of file declared by user.
     * Lower characters, and numbers [a-z0-9].
     *
     * @ORM\Column(type="string", length=32)
     * @Assert\Length(max="32")
     * @var string
     */
    protected $checksum;

    /**
     * Name for download name
     *
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    protected $name;

    /**
     * File MIME type
     *
     * @ORM\Column(type="string", length=128)
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(name="size", type="bigint", options={"unsigned"=true})
     * @Assert\GreaterThan(value="1")
     * @Assert\Type(type="numeric")
     */
    protected $size;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $expirationDate;

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
     * @ORM\OneToMany(targetEntity="FileBlock", mappedBy="file", cascade={"remove"})
     */
    protected $fileBlocks;

    /**
     * @ORM\Column(name="complete", type="boolean", options={"default" = false})
     * @var integer
     */
    protected $complete = false;

    public function __construct()
    {
        $this->fileBlocks = new ArrayCollection();
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

    /**
     * Set fileId
     *
     * @param  string $fileId
     * @return File
     */
    public function setFileId($fileId)
    {
        $this->fileId = $fileId;

        return $this;
    }

    /**
     * Get fileId.
     *
     * @return string
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * Set fileHash.
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
     * Set name
     *
     * @param  string $name
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
     * Set size
     *
     * @param  integer $size
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
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set expirationDate
     *
     * @param  \DateTime $expirationDate
     * @return File
     */
    public function setExpirationDate(\DateTime $expirationDate = null)
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
     * @param  \DateTime $createdAt
     * @return File
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
     * @return File
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
     * Set type
     *
     * @param  string $type
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
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
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

    /**
     * Add fileBlocks
     *
     * @param  \Enginewerk\EmissionBundle\Entity\FileBlock $fileBlocks
     * @return File
     */
    public function addFileBlock(\Enginewerk\EmissionBundle\Entity\FileBlock $fileBlocks)
    {
        $this->fileBlocks[] = $fileBlocks;

        return $this;
    }

    /**
     * Remove fileBlocks
     *
     * @param \Enginewerk\EmissionBundle\Entity\FileBlock $fileBlocks
     */
    public function removeFileBlock(\Enginewerk\EmissionBundle\Entity\FileBlock $fileBlocks)
    {
        $this->fileBlocks->removeElement($fileBlocks);
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
     * Set checksum
     *
     * @param  string $checksum
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
     * Set user
     *
     * @param  \Enginewerk\EmissionBundle\Entity\User $user
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
     * @return \Enginewerk\EmissionBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set complete
     *
     * @param  boolean $complete
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
     * @return boolean
     */
    public function getComplete()
    {
        return $this->complete;
    }
}
