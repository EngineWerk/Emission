<?php
namespace Enginewerk\EmissionBundle\Service;

use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\Entity\FileBlock;

interface FileReadServiceInterface
{
    /**
     * @param int $fileId
     *
     * @return int
     */
    public function getTotalSize($fileId);

    /**
     * @param string $fileName
     * @param string $fileChecksum
     * @param int $fileSize
     *
     * @return File|null
     */
    public function findFile($fileName, $fileChecksum, $fileSize);

    /**
     * @param int $fileId
     * @param int $rangeStart
     * @param int $rangeEnd
     *
     * @return FileBlock|null
     */
    public function findFileBlock($fileId, $rangeStart, $rangeEnd);
}
