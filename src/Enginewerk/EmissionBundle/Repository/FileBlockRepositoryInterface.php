<?php
namespace Enginewerk\EmissionBundle\Repository;

use Enginewerk\EmissionBundle\Entity\FileBlock;

interface FileBlockRepositoryInterface
{
    /**
     * @param string $fileId
     * @param int $rangeStart
     * @param int $rangeEnd
     *
     * @return FileBlock
     */
    public function finOneById($fileId, $rangeStart, $rangeEnd);

    /**
     * @param string $fileHash
     *
     * @return int
     */
    public function getUsedBlocksNumber($fileHash);

    /**
     * @param string $fileId
     *
     * @return int
     */
    public function getTotalSize($fileId);

    /**
     * Returns FileBlocks ordered by Range
     *
     * @param int $fileId
     *
     * @return FileBlock[]
     */
    public function findByFileId($fileId);

    /**
     * @param FileBlock $file
     *
     * @return void
     */
    public function remove(FileBlock $file);
}
