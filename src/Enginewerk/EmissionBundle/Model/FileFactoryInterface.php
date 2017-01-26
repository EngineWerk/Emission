<?php
namespace Enginewerk\EmissionBundle\Model;

use Enginewerk\EmissionBundle\Entity\File as FileEntity;

interface FileFactoryInterface
{
    /**
     * @param FileEntity $file
     *
     * @return File
     */
    public function createFromEntity(FileEntity $file);
}
