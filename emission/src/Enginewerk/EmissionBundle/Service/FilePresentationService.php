<?php
namespace Enginewerk\EmissionBundle\Service;

use Enginewerk\EmissionBundle\Entity\File as FileEntity;
use Enginewerk\EmissionBundle\Presentation\Model\FileView;
use Enginewerk\EmissionBundle\Presentation\Model\FileViewCollection;
use Enginewerk\EmissionBundle\Repository\FileRepositoryInterface;
use Enginewerk\EmissionBundle\Storage\FileNotFoundException;

final class FilePresentationService implements FileViewFinderInterface
{
    /** @var FileRepositoryInterface */
    private $fileRepository;

    /**
     * @param FileRepositoryInterface $fileRepository
     */
    public function __construct(FileRepositoryInterface $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
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
    public function findExpiredFiles(\DateTimeInterface $nowDate = null)
    {
        $collection = new FileViewCollection();

        $files = $this->fileRepository->getExpiredFiles($nowDate);

        foreach ($files as $file) {
            $fileView = $this->createFileViewFromFileEntity($file);
            $collection->add($fileView);
        }

        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function findByPublicIdentifier($identifier)
    {
        $file = $this->fileRepository->findByPublicIdentifier($identifier);

        if ($file) {
            return $this->createFileViewFromFileEntity($file);
        }

        return null;
    }

    public function getByPublicIdentifier($identifier)
    {
        $file = $this->findByPublicIdentifier($identifier);

        if (null === $file) {
            throw new FileNotFoundException(sprintf('File identified by "%s" was not found', $identifier));
        }

        return $file;
    }

    /**
     * @param \DateTimeInterface $createdAfter
     *
     * @deprecated Will be removed permanently
     *
     * @return string[]
     */
    public function findAllAsArray(\DateTimeInterface $createdAfter)
    {
        return $this->fileRepository->findAllAsArray($createdAfter);
    }

    /**
     * @param FileEntity $file
     *
     * @return FileView
     */
    private function createFileViewFromFileEntity(FileEntity $file)
    {
        return new FileView(
            $file->getId(),
            $file->getPublicIdentifier(),
            $file->getChecksum(),
            $file->getName(),
            $file->getType(),
            $file->getSize(),
            \DateTimeImmutable::createFromMutable($file->getExpirationDate()),
            \DateTimeImmutable::createFromMutable($file->getCreatedAt()),
            \DateTimeImmutable::createFromMutable($file->getUpdatedAt()),
            $file->getComplete(),
            $file->getUser()->getUsername()
        );
    }
}
