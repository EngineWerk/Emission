<?php
namespace Enginewerk\EmissionBundle\Service;

use Enginewerk\EmissionBundle\Entity\File as FileEntity;
use Enginewerk\EmissionBundle\Entity\FileBlock;
use Enginewerk\EmissionBundle\Storage\FileNotFoundException;
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
     * @return FileEntity
     */
    public function createFile($fileName, $fileChecksum, $fileSize, User $user, $mimeType);

    /**
     * @param string $publicIdentifier
     *
     * @throws FileNotFoundException
     *
     * @return void
     */
    public function setFileAsComplete($publicIdentifier);

    /**
     * @param FileEntity $file
     * @param string $fileHash
     * @param int $size
     * @param int $rangeStart
     * @param int $rangeEnd
     *
     * @return FileBlock
     */
    public function createFileBlock(FileEntity $file, $fileHash, $size, $rangeStart, $rangeEnd);
}
