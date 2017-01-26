<?php
namespace Enginewerk\EmissionBundle\Service;

use DateTimeInterface;
use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\Entity\FileBlock;
use Enginewerk\EmissionBundle\Storage\FileNotFoundException;
use Enginewerk\EmissionBundle\Storage\InvalidFileIdentifierException;

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

    /**
     * @param int $fileId
     * @param int $chunkRangeStart
     * @param int $chunkRangeEnd
     *
     * @return bool
     */
    public function hasFileFileBlock($fileId, $chunkRangeStart, $chunkRangeEnd);

    /**
     * @param string $fileName
     * @param string $fileChecksum
     * @param int $fileSize
     *
     * @return File|null
     */
    public function findOneByNameAndChecksumAndSize($fileName, $fileChecksum, $fileSize);

    /**
     * @param string $shortFileIdentifier
     *
     * @throws FileNotFoundException
     * @throws InvalidFileIdentifierException
     *
     * @return File
     *
     */
    public function getByShortFileIdentifier($shortFileIdentifier);

    /**
     * @param string $identifier
     *
     * @throws InvalidFileIdentifierException
     *
     * @return File|null
     *
     */
    public function findByShortIdentifier($identifier);

    /**
     * @param string $fileHash
     *
     * @return int
     */
    public function getUsedBlocksNumber($fileHash);

    /**
     * @param int $fileId
     *
     * @return FileBlock[]
     */
    public function findBlocksByFileId($fileId);

    /**
     * @param DateTimeInterface $createdAfter
     *
     * @return string[]
     */
    public function getFilesForJsonApi(DateTimeInterface $createdAfter);
}
