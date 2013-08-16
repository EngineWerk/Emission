<?php

namespace Enginewerk\EmissionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

use Enginewerk\EmissionBundle\Generator\Hash;

/**
 * File
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="file")
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
     * @var string
     */
    protected $fileHash;

    /**
     * Name for download name
     * 
     * @ORM\Column(type="string", length=255)
     * at Assert\NotBlank
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
     * @var integer
     */
    protected $size;
    
    /**
     * @ORM\Column(type="datetime")
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
     * @ORM\OneToMany(targetEntity="FileBlob", mappedBy="file", cascade={"remove"})
     */
    protected $fileBlobs;
    
    /**
     * @ORM\Column(name="isComplete", type="boolean", options={"default" = false})
     * @var integer
     */
    protected $isComplete;

    public function __construct()
    {
        $this->fileBlobs = new ArrayCollection();
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
     * @param string $fileId
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
     *
     * @param string $fileHash
     * @return File
     */
    public function setFileHash($fileHash)
    {
        throw new \Exception('"FileHash" value is immunable');
        
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
     * @param string $name
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
     * Set extensionName
     *
     * @param string $extensionName
     * @return File
     */
    public function setExtensionName($extensionName)
    {
        $this->extensionName = $extensionName;
    
        return $this;
    }

    /**
     * Get extensionName
     *
     * @return string 
     */
    public function getExtensionName()
    {
        return $this->extensionName;
    }

    /**
     * Set size
     *
     * @param integer $size
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
     * @param \DateTime $expirationDate
     * @return File
     */
    public function setExpirationDate(\DateTime $expirationDate)
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
     * @param \DateTime $updatedAt
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
     * @param string $type
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
     * Add fileBlobs
     *
     * @param \Enginewerk\EmissionBundle\Entity\FileBlob $fileBlobs
     * @return File
     */
    public function addFileBlob(\Enginewerk\EmissionBundle\Entity\FileBlob $fileBlobs)
    {
        $this->fileBlobs[] = $fileBlobs;
    
        return $this;
    }

    /**
     * Remove fileBlobs
     *
     * @param \Enginewerk\EmissionBundle\Entity\FileBlob $fileBlobs
     */
    public function removeFileBlob(\Enginewerk\EmissionBundle\Entity\FileBlob $fileBlobs)
    {
        $this->fileBlobs->removeElement($fileBlobs);
    }

    /**
     * Get fileBlobs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFileBlobs()
    {
        return $this->fileBlobs;
    }

    /**
     * Set isComplete
     *
     * @param boolean $isComplete
     * @return File
     */
    public function setIsComplete($isComplete)
    {
        $this->isComplete = $isComplete;
    
        return $this;
    }

    /**
     * Get isComplete
     *
     * @return boolean 
     */
    public function getIsComplete()
    {
        return $this->isComplete;
    }
}