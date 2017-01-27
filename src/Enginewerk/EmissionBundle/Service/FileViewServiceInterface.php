<?php
namespace Enginewerk\EmissionBundle\Service;

use Enginewerk\FileManagementBundle\Entity\File;

interface FileViewServiceInterface
{
    /**
     * @param File $file
     *
     * @return string[]
     */
    public function createResponseForIncompleteFile(File $file);

    /**
     * @param File $file
     *
     * @return string[]
     */
    public function createResponseForCompleteFile(File $file);
}
