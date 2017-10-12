<?php
namespace Enginewerk\ResumableBundle\FileUpload\Response;

class IncompleteFileResponse
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $publicIdentifier;

    /** @var string */
    protected $name;

    /** @var string */
    protected $type;

    /** @var int */
    protected $size;

    /**
     * @param int $id
     * @param string $publicIdentifier
     * @param string $name
     * @param string $type
     * @param int $size
     */
    public function __construct($id, $publicIdentifier, $name, $type, $size)
    {
        $this->id = $id;
        $this->publicIdentifier = $publicIdentifier;
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
    }

    /**
     * @return string[]
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'file_id' => $this->publicIdentifier,
            'name' => $this->name,
            'type' => $this->type,
            'size' => $this->size,
        ];
    }
}
