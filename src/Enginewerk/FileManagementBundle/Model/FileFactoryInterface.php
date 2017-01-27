<?php
namespace Enginewerk\FileManagementBundle\Model;

use Enginewerk\FileManagementBundle\Entity\File as FileEntity;

interface FileFactoryInterface
{
    /**
     * @param FileEntity $file
     *
     * @return File
     */
    public function createFromEntity(FileEntity $file);
}
