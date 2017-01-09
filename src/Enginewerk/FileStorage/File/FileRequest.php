<?php
namespace Enginewerk\FileStorage\File;

class FileRequest implements FileRequestInterface
{
    /**
     * @var string
     */
    protected $fileIdentifier;

    /**
     * @param string $fileIdentifier
     */
    public function __construct($fileIdentifier)
    {
        $this->fileIdentifier = $fileIdentifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->fileIdentifier;
    }
}
