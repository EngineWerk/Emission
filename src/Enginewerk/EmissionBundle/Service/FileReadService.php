<?php
namespace Enginewerk\EmissionBundle\Service;

use DateTimeInterface;
use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\Repository\FileRepositoryInterface;

final class FileReadService
{
    /** @var  FileRepositoryInterface */
    private $fileRepository;

    /**
     * @param FileRepositoryInterface $fileRepository
     */
    public function __construct(FileRepositoryInterface $fileRepository)
    {
        $this->fileRepository = $fileRepository;
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
     * @return File[]
     */
    public function findAllFiles()
    {
        return $this->fileRepository->getFiles();
    }
}
