<?php
namespace Enginewerk\EmissionBundle\Storage;

use Enginewerk\EmissionBundle\Storage\Model\File;

interface FileFinderInterface
{
    /**
     * @param string $publicIdentifier
     *
     * @throws FileNotFoundException
     *
     * @return File
     */
    public function getFileByPublicIdentifier($publicIdentifier);

    /**
     * @param string $fileName
     * @param string $fileChecksum
     * @param int $fileSize
     *
     * @return File|null
     */
    public function findFile($fileName, $fileChecksum, $fileSize);
}
