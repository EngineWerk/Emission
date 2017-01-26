<?php
namespace Enginewerk\EmissionBundle\Service;

use DateTimeInterface;
use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\Model\File as FileModel;
use Enginewerk\EmissionBundle\Model\FileCollection;
use Enginewerk\EmissionBundle\Model\FileFactoryInterface;
use Enginewerk\EmissionBundle\Repository\FileBlockRepositoryInterface;
use Enginewerk\EmissionBundle\Repository\FileRepositoryInterface;
use Enginewerk\EmissionBundle\Storage\FileNotFoundException;
use Enginewerk\EmissionBundle\Storage\InvalidFileIdentifierException;

class FileReadService implements FileReadServiceInterface
{
    /** @var  FileRepositoryInterface */
    private $fileRepository;

    /** @var  FileBlockRepositoryInterface */
    private $fileBlockRepository;

    /** @var  FileFactoryInterface */
    private $fileFactory;

    /**
     * @param FileRepositoryInterface $fileRepository
     * @param FileBlockRepositoryInterface $fileBlockRepository
     * @param FileFactoryInterface $fileFactory
     */
    public function __construct(
        FileRepositoryInterface $fileRepository,
        FileBlockRepositoryInterface $fileBlockRepository,
        FileFactoryInterface $fileFactory
    ) {
        $this->fileRepository = $fileRepository;
        $this->fileBlockRepository = $fileBlockRepository;
        $this->fileFactory = $fileFactory;
    }

    /**
     * @return FileCollection
     */
    public function findAllFiles()
    {
        $fileCollection = new FileCollection();
        foreach ($this->fileRepository->getFiles() as $fileEntity) {
            $fileCollection->add($this->fileFactory->createFromEntity($fileEntity));
        }

        return $fileCollection;
    }

    /**
     * @param string $shortIdentifier
     *
     * @return FileModel
     */
    public function findFileByShortIdentifier($shortIdentifier)
    {
        $fileEntity = $this->findByShortIdentifier($shortIdentifier);

        return $this->fileFactory->createFromEntity($fileEntity);
    }

    /**
     * @inheritdoc
     */
    public function getFilesForJsonApi(DateTimeInterface $createdAfter)
    {
        return $this->fileRepository->getFilesForJsonApi($createdAfter);
    }

    /**
     * @param DateTimeInterface|null $nowDate
     *
     * @return File[]
     */
    public function getExpiredFiles(\DateTimeInterface $nowDate = null)
    {
        return $this->fileRepository->getExpiredFiles($nowDate);
    }

    /**
     * @inheritdoc
     */
    public function getTotalSize($fileId)
    {
        return $this->fileBlockRepository->getTotalSize($fileId);
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
    public function findFileBlock($fileId, $rangeStart, $rangeEnd)
    {
        return $this->fileBlockRepository->findByFileIdAndRangeStartAndRangeEnd($fileId, $rangeStart, $rangeEnd);
    }

    /**
     * @inheritdoc
     */
    public function hasFileFileBlock($fileId, $chunkRangeStart, $chunkRangeEnd)
    {
        return null !== $this->fileBlockRepository
            ->findByFileIdAndRangeStartAndRangeEnd($fileId, $chunkRangeStart, $chunkRangeEnd);
    }

    /**
     * @inheritdoc
     */
    public function findOneByNameAndChecksumAndSize($fileName, $fileChecksum, $fileSize)
    {
        return $this->fileRepository->findOneByNameAndChecksumAndSize($fileName, $fileChecksum, $fileSize);
    }

    /**
     * @inheritdoc
     */
    public function getByShortFileIdentifier($shortFileIdentifier)
    {
        $file = $this->findByShortIdentifier($shortFileIdentifier);

        if (null !== $file) {
            return $file;
        } else {
            throw new FileNotFoundException(sprintf('File with key "%s" not found.', $shortFileIdentifier));
        }
    }

    /**
     * @inheritdoc
     */
    public function findByShortIdentifier($identifier)
    {
        if (mb_strlen($identifier) === 0) {
            throw new InvalidFileIdentifierException('File short identifier cannot be empty.');
        }

        return $this->fileRepository->findOneByShortIdentifier($identifier);
    }

    /**
     * @inheritdoc
     */
    public function getUsedBlocksNumber($fileHash)
    {
        return $this->fileBlockRepository->getUsedBlocksNumber($fileHash);
    }

    /**
     * @inheritdoc
     */
    public function findBlocksByFileId($fileId)
    {
        return $this->fileBlockRepository->findByFileId($fileId);
    }
}
