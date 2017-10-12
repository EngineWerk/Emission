<?php
namespace Enginewerk\EmissionBundle\Service;

use Enginewerk\EmissionBundle\Repository\FileBlockRepositoryInterface;
use Enginewerk\EmissionBundle\Repository\FileRepositoryInterface;

class FileReadService implements FileReadServiceInterface
{
    /** @var  FileRepositoryInterface */
    protected $fileRepository;

    /** @var  FileBlockRepositoryInterface */
    protected $fileBlockRepository;

    /**
     * @param FileRepositoryInterface $fileRepository
     * @param FileBlockRepositoryInterface $fileBlockRepository
     */
    public function __construct(
        FileRepositoryInterface $fileRepository,
        FileBlockRepositoryInterface $fileBlockRepository
    ) {
        $this->fileRepository = $fileRepository;
        $this->fileBlockRepository = $fileBlockRepository;
    }

    /**
     * @inheritdoc
     */
    public function getTotalSize($publicIdentifier)
    {
        return $this->fileBlockRepository->getTotalSize($publicIdentifier);
    }

    /**
     * @inheritdoc
     */
    public function findFile($fileName, $fileChecksum, $fileSize)
    {
        return $this->fileRepository->findOneByNameAndChecksumAndSize($fileName, $fileChecksum, (int) $fileSize);
    }

    /**
     * @inheritdoc
     */
    public function findFileBlock($publicIdentifier, $rangeStart, $rangeEnd)
    {
        return $this->fileBlockRepository->findByFileIdAndRangeStartAndRangeEnd($publicIdentifier, $rangeStart, $rangeEnd);
    }
}
