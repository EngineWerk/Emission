<?php
namespace Enginewerk\EmissionBundle\Presentation\Model;

final class FileView
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
     * @var \DateTimeImmutable
     */
    private $expirationDate;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable
     */
    private $updatedAt;

    /**
     * @var bool
     */
    private $complete;

    /**
     * @var string
     */
    private $userName;

    /**
     * @param int $id
     * @param string $fileId
     * @param string $checksum
     * @param string $name
     * @param string $type
     * @param int $size
     * @param \DateTimeImmutable $expirationDate
     * @param \DateTimeImmutable $createdAt
     * @param \DateTimeImmutable $updatedAt
     * @param bool $complete
     * @param string $userName
     */
    public function __construct($id, $fileId, $checksum, $name, $type, $size, \DateTimeImmutable $expirationDate, \DateTimeImmutable $createdAt, \DateTimeImmutable $updatedAt, $complete, $userName)
    {
        $this->fileId = $fileId;
        $this->checksum = $checksum;
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->expirationDate = $expirationDate;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->complete = $complete;
        $this->userName = $userName;
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * @return string
     */
    public function getChecksum()
    {
        return $this->checksum;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        return $this->complete;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }
}
