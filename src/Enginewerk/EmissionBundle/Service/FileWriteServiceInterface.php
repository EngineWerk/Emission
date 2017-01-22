<?php
namespace Enginewerk\EmissionBundle\Service;

use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\Entity\FileBlock;
use Enginewerk\UserBundle\Entity\User;

interface FileWriteServiceInterface
{
    /**
     * @param string $fileName
     * @param string $fileChecksum
     * @param int $fileSize
     * @param User $user
     * @param string $mimeType
     *
     * @return File
     */
    public function createFile($fileName, $fileChecksum, $fileSize, User $user, $mimeType);

    /**
     * @param File $file
     */
    public function setFileAsComplete(File $file);

    /**
     * @param File $file
     * @param string $fileHash
     * @param int $size
     * @param int $rangeStart
     * @param int $rangeEnd
     *
     * @return FileBlock
     */
    public function createFileBlock(File $file, $fileHash, $size, $rangeStart, $rangeEnd);
}
