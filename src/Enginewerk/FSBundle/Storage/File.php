<?php

namespace Enginewerk\FSBundle\Storage;

/**
 * Description of File
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class File
{
    /**
     * @var string
     */
    private $checksum;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var string
     */
    private $pathname;
    
    /**
     * Set checksum
     *
     * @param  string      $checksum
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
     * Get pathname
     *
     * @return string
     */
    public function getPathname()
    {
        return $this->pathname;
    }

    /**
     * Set pathname
     *
     * @param  string      $pathname
     * @return File
     */
    public function setPathname($pathname)
    {
        $this->pathname = $pathname;

        return $this;
    }

    /**
     * Set size
     *
     * @param  integer     $size
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
}
