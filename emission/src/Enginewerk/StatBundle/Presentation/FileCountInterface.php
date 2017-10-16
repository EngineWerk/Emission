<?php
namespace Enginewerk\StatBundle\Presentation;

interface FileCountInterface
{
    /**
     * @return int
     */
    public function getFilesCount();

    /**
     * @return <int,string>[]
     */
    public function getFileTypesCount();
}
