<?php
namespace Enginewerk\EmissionBundle\Repository;

use Enginewerk\EmissionBundle\Entity\FileBlock;

interface FileBlockRepositoryInterface
{
    /**
     * @param string $fileHash
     *
     * @return int
     */
    public function getUsedBlocksNumber($fileHash);

    /**
     * @param string $publicIdentifier
     *
     * @return int
     */
    public function getTotalSize($publicIdentifier);

    /**
     * Returns FileBlocks ordered by Range
     *
     * @param int $fileId
     *
     * @return FileBlock[]
     */
    public function findByFileId($fileId);

    /**
     * @param string $publicIdentifier
     * @param int $rangeStart
     * @param int $rangeEnd
     *
     * @return FileBlock|null
     */
    public function findByFileIdAndRangeStartAndRangeEnd($publicIdentifier, $rangeStart, $rangeEnd);

    /**
     * @param FileBlock $fileBlock
     *
     * @return void
     */
    public function remove(FileBlock $fileBlock);

    /**
     * @param FileBlock $fileBlock
     *
     * @return void
     */
    public function persist(FileBlock $fileBlock);
}
