<?php
namespace Enginewerk\ResumableBundle\FileUpload;

class FileRequest
{
    /** @var int */
    protected $resumableChunkNumber;

    /** @var int  */
    protected $resumableTotalChunks;

    /** @var int  */
    protected $resumableChunkSize;

    /** @var int  */
    protected $resumableCurrentChunkSize;

    /** @var int  */
    protected $resumableCurrentStartByte;

    /** @var int  */
    protected $resumableCurrentEndByte;

    /** @var int  */
    protected $resumableTotalSize;

    /** @var string  */
    protected $resumableType;

    /** @var string  */
    protected $resumableIdentifier;

    /** @var string  */
    protected $resumableFilename;

    /** @var string  */
    protected $resumableRelativePath;

    /**
     * @param string[] $resumableRequest
     */
    public function __construct(array $resumableRequest)
    {
        $this->resumableChunkNumber = (int) $resumableRequest['resumableChunkNumber'];
        $this->resumableChunkSize = (int) $resumableRequest['resumableChunkSize'];
        $this->resumableCurrentChunkSize = (int) $resumableRequest['resumableCurrentChunkSize'];
        $this->resumableCurrentStartByte = (int) $resumableRequest['resumableCurrentStartByte'];
        $this->resumableCurrentEndByte = (int) $resumableRequest['resumableCurrentEndByte'];
        $this->resumableTotalSize = (int) $resumableRequest['resumableTotalSize'];
        $this->resumableType = (string) $resumableRequest['resumableType'];
        $this->resumableIdentifier = (string) $resumableRequest['resumableIdentifier'];
        $this->resumableFilename = (string) $resumableRequest['resumableFilename'];
        $this->resumableRelativePath = (string) $resumableRequest['resumableRelativePath'];
        $this->resumableTotalChunks = (int) $resumableRequest['resumableTotalChunks'];
    }

    /**
     * @return int
     */
    public function getResumableChunkNumber()
    {
        return $this->resumableChunkNumber;
    }

    /**
     * @return int
     */
    public function getResumableTotalChunks()
    {
        return $this->resumableTotalChunks;
    }

    /**
     * @return int
     */
    public function getResumableChunkSize()
    {
        return $this->resumableChunkSize;
    }

    /**
     * @return int
     */
    public function getResumableCurrentChunkSize()
    {
        return $this->resumableCurrentChunkSize;
    }

    /**
     * @return int
     */
    public function getResumableCurrentStartByte()
    {
        return $this->resumableCurrentStartByte;
    }

    /**
     * @return int
     */
    public function getResumableCurrentEndByte()
    {
        return $this->resumableCurrentEndByte;
    }

    /**
     * @return int
     */
    public function getResumableTotalSize()
    {
        return $this->resumableTotalSize;
    }

    /**
     * @return string
     */
    public function getResumableType()
    {
        return $this->resumableType;
    }

    /**
     * @return string
     */
    public function getResumableIdentifier()
    {
        return $this->resumableIdentifier;
    }

    /**
     * @return string
     */
    public function getResumableFilename()
    {
        return $this->resumableFilename;
    }

    /**
     * @return string
     */
    public function getResumableRelativePath()
    {
        return $this->resumableRelativePath;
    }
}
