<?php
namespace Enginewerk\EmissionBundle\Service;

use Enginewerk\EmissionBundle\Entity\File as FileEntity;
use Enginewerk\EmissionBundle\Entity\FileBlock;

interface FileReadServiceInterface
{
    /**
     * @param string $publicIdentifier
     *
     * @return int
     */
    public function getTotalSize($publicIdentifier);

    /**
     * @param string $fileName
     * @param string $fileChecksum
     * @param int $fileSize
     *
     * @return FileEntity|null
     */
    public function findFile($fileName, $fileChecksum, $fileSize);

    /**
     * @param string $publicIdentifier
     * @param int $rangeStart
     * @param int $rangeEnd
     *
     * @return FileBlock|null
     */
    public function findFileBlock($publicIdentifier, $rangeStart, $rangeEnd);
}
