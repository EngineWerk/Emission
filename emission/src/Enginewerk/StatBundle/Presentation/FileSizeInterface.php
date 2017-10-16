<?php
namespace Enginewerk\StatBundle\Presentation;

interface FileSizeInterface
{
    /**
     * @return int bytes
     */
    public function getFilesSize();

    /**
     * @return int bytes
     */
    public function getFilesSizeReal();
}
