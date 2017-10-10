<?php
namespace Enginewerk\EmissionBundle\Service;

use DateTimeInterface;
use Enginewerk\ApplicationBundle\Response\ServiceResponse;
use Enginewerk\ApplicationBundle\Response\WebApplicationResponse;
use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\Presentation\Model\FileView;
use Enginewerk\EmissionBundle\Presentation\Model\FileViewCollection;
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
     * @param string $fileName
     * @param string $fileChecksum
     * @param int $fileSize
     * @param int $chunkRangeStart
     * @param int $chunkRangeEnd
     *
     * @return ServiceResponse
     */
    public function findFileChunk($fileName, $fileChecksum, $fileSize, $chunkRangeStart, $chunkRangeEnd)
    {
        $response = new WebApplicationResponse();

        $file = $this->fileRepository
            ->findOneByNameAndChecksumAndSize($fileName, $fileChecksum, $fileSize);

        if ($file) {
            if ($this->hasFileFileBlock($file->getId(), $chunkRangeStart, $chunkRangeEnd)) {
                $response->success('Block found');
                $responseCode = 200;
            } else {
                $response->success('Block not found');
                $responseCode = 306;
            }
        } else {
            $response->error(sprintf('File "%s" not found', $fileName));
            $responseCode = 306;
        }

        return new ServiceResponse($responseCode, $response->toArray());
    }

    /**
     * @param int $fileId
     * @param int $chunkRangeStart
     * @param int $chunkRangeEnd
     *
     * @return bool
     */
    protected function hasFileFileBlock($fileId, $chunkRangeStart, $chunkRangeEnd)
    {
        return null !== $this->fileBlockRepository
            ->findByFileIdAndRangeStartAndRangeEnd($fileId, $chunkRangeStart, $chunkRangeEnd);
    }

    /**
     * @param DateTimeInterface $createdAfter
     *
     * @return string[]
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
     * @return FileViewCollection
     */
    public function findAllFiles()
    {
        $collection = new FileViewCollection();

        $files = $this->fileRepository->getFiles();

        foreach ($files as $file) {
            $fileView = $this->createFileViewFromFileEntity($file);
            $collection->add($fileView);
        }

        return $collection;
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
     * @param File $file
     *
     * @return FileView
     */
    private function createFileViewFromFileEntity(File $file)
    {
        return new FileView(
            $file->getId(),
            $file->getPublicIdentifier(),
            $file->getChecksum(),
            $file->getName(),
            $file->getType(),
            $file->getSize(), \DateTimeImmutable::createFromMutable($file->getExpirationDate()),
            \DateTimeImmutable::createFromMutable($file->getCreatedAt()),
            \DateTimeImmutable::createFromMutable($file->getUpdatedAt()),
            $file->getComplete(),
            $file->getUser()->getUsername()
        );
    }

    /**
     * @param string $identifier
     *
     * @return FileView|null
     */
    public function findByShortIdentifier($identifier)
    {
        $file = $this->fileRepository->findByPublicIdentifier($identifier);

        if ($file) {
            return $this->createFileViewFromFileEntity($file);
        }

        return null;
    }
}
