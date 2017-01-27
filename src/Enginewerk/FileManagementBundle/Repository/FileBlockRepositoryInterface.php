<?php
namespace Enginewerk\FileManagementBundle\Repository;

use Enginewerk\FileManagementBundle\Entity\FileBlock;

interface FileBlockRepositoryInterface
{
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
     * @param int $fileId
     * @param int $rangeStart
     * @param int $rangeEnd
     *
     * @return FileBlock|null
     */
    public function findByFileIdAndRangeStartAndRangeEnd($fileId, $rangeStart, $rangeEnd);

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
