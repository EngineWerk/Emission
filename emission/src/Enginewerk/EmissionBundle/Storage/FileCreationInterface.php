<?php
namespace Enginewerk\EmissionBundle\Storage;

interface FileCreationInterface
{
    /**
     * @param string $fileName
     * @param string $fileChecksum
     * @param int $fileSize
     * @param string $userIdentifier
     * @param string $mimeType
     *
     * @return string Public Identifier
     */
    public function createFile($fileName, $fileChecksum, $fileSize, $userIdentifier, $mimeType);

    /**
     * @param string $publicIdentifier
     * @param string $filePartHash
     * @param int $size
     * @param int $rangeStart
     * @param int $rangeEnd
     *
     * @return void
     */
    public function createFilePart($publicIdentifier, $filePartHash, $size, $rangeStart, $rangeEnd);

    /**
     * @param string $publicIdentifier
     *
     * @throws FileNotFoundException
     *
     * @return void
     */
    public function setFileAsComplete($publicIdentifier);
}
