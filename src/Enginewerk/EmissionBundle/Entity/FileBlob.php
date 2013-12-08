<?php

namespace Enginewerk\EmissionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Enginewerk\EmissionBundle\Entity\File;

/**
 * File
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="file_blob")
 */
class FileBlob
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
     * @Assert\File(maxSize="16777216")
     */
    private $fileBlob;
    
    private $temp;
    
    /**
     * @ORM\ManyToOne(targetEntity="File", inversedBy="fileBlobs", cascade={"persist", "refresh"})
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
     * @param string $fileHash
     * @return File
     */
    public function setFileHash($fileHash)
    {
        throw new \Exception('"FileHash" value is constant');
        
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
     * @param integer $size
     * @return FileBlob
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
     * @param \DateTime $createdAt
     * @return FileBlob
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
     * @return FileBlob
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
     * 
     * @return string
     */
    public function getAbsolutePath()
    {
        return null === $this->fileHash
            ? null
            : $this->getUploadRootDir().'/'. $this->getDeepDirFromFileName($this->getFileHash()) . '/' . $this->getFileHash();
    }

    /**
     * 
     * @return string
     */
    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../app/local_fs/main' ;
    }
    
    protected function getDeepDirFromFileName($fileName)
    {
        return sprintf('%s/%s/%s', $fileName[0], $fileName[1], $fileName[2]);
    }
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFileBlob(UploadedFile $file = null)
    {
        $this->fileBlob = $file;
        
        // check if we have an old file path
        if (isset($this->fileHash)) {
            // store the old name to delete after the update
            $this->temp = $this->fileHash;
            $this->fileHash = null;
        } else {
            $this->fileHash = 'initial';
        }
    }
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFileBlob()) {
            
            $fileHash = sha1(uniqid(mt_rand(), true));
            $createdAt = new \DateTime();
            $createdAt->setTimestamp(time());
            
            if(!$this->getFile()) {
                
                
            }
            
            if(null === $this->getRangeStart()) {
                $this->setRangeStart(0);
                $this->setRangeEnd($this->getFileBlob()->getSize());
            }
            
            $this->fileHash = $fileHash;
            $this->setSize($this->getFileBlob()->getSize());

            $this->setUpdatedAt($createdAt);
            $this->setCreatedAt($createdAt);
        }
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFileBlob()
    {
        return $this->fileBlob;
    }
    
   /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getFileBlob()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFileBlob()->move($this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->getDeepDirFromFileName($this->getFileHash()), $this->getFileHash());

        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            unlink($this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->getDeepDirFromFileName($this->temp). $this->temp);
            // clear the temp image path
            $this->temp = null;
        }
        $this->fileBlob = null;
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $filePath = $this->getAbsolutePath();
        if(file_exists($filePath)) {
            unlink($filePath);
        }
    }
    

    /**
     * Set rangeStart
     *
     * @param integer $rangeStart
     * @return FileBlob
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
     * @param integer $rangeEnd
     * @return FileBlob
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
     * @param \Enginewerk\EmissionBundle\Entity\File $file
     * @return FileBlob
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
     * @param integer $fileId
     * @return FileBlob
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