<?php
namespace Enginewerk\EmissionBundle\Storage\Manager;

interface CreateFileInterface
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
}
