<?php
namespace Enginewerk\FileManagementBundle\Model;

final class File
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $shortIdentifier;

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
    private $mimeType;

    /**
     * @var int
     */
    private $size;

    /** @var  string */
    private $humanReadableSize;

    /**
     * @var bool
     */
    private $isComplete;

    /**
     * @var string
     */
    private $ownerName;

    /**
     * @param int $id
     * @param string $shortIdentifier
     * @param string $checksum
     * @param string $name
     * @param string $mimeType
     * @param int $size
     * @param string $humanReadableSize
     * @param bool $isComplete
     * @param string $ownerName
     */
    public function __construct(
        $id, $shortIdentifier, $checksum, $name, $mimeType, $size, $humanReadableSize, $isComplete, $ownerName
    ) {
        $this->id = $id;
        $this->shortIdentifier = $shortIdentifier;
        $this->checksum = $checksum;
        $this->name = $name;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->isComplete = $isComplete;
        $this->humanReadableSize = $humanReadableSize;
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
    public function getShortIdentifier()
    {
        return $this->shortIdentifier;
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
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        return $this->isComplete;
    }

    /**
     * @return string
     */
    public function getOwnerName()
    {
        return $this->ownerName;
    }

    /**
     * @return string
     */
    public function getHumanReadableSize()
    {
        return $this->humanReadableSize;
    }
}
