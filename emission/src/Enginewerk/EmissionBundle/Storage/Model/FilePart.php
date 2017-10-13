<?php
namespace Enginewerk\EmissionBundle\Storage\Model;

final class FilePart
{
    /** @var string */
    private $identifier;

    /** @var int */
    private $rangeStart;

    /** @var int */
    private $rangeEnd;

    /**
     * @param string $identifier
     * @param int $rangeStart
     * @param int $rangeEnd
     */
    public function __construct($identifier, $rangeStart, $rangeEnd)
    {
        $this->identifier = $identifier;
        $this->rangeStart = (int) $rangeStart;
        $this->rangeEnd = (int) $rangeEnd;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return int
     */
    public function getRangeStart()
    {
        return $this->rangeStart;
    }

    /**
     * @return int
     */
    public function getRangeEnd()
    {
        return $this->rangeEnd;
    }
}
