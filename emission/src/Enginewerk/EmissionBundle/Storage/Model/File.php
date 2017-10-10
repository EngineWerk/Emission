<?php
namespace Enginewerk\EmissionBundle\Storage\Model;

final class File
{
    /** @var string */
    private $publicIdentifier;

    /** @var string */
    private $name;

    /** @var int */
    private $size;

    /** @var string */
    private $type;

    /** @var FilePartCollection */
    private $filePartCollection;

    /**
     * @param string $publicIdentifier
     * @param string $name
     * @param int $size
     * @param string $type
     * @param FilePartCollection $filePartCollection
     */
    public function __construct($publicIdentifier, $name, $size, $type, FilePartCollection $filePartCollection)
    {
        $this->publicIdentifier = $publicIdentifier;
        $this->name = $name;
        $this->size = (int) $size;
        $this->type = $type;
        $this->filePartCollection = $filePartCollection;
    }

    /**
     * @return string
     */
    public function getPublicIdentifier()
    {
        return $this->publicIdentifier;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return FilePartCollection
     */
    public function getFilePartCollection()
    {
        return $this->filePartCollection;
    }
}
