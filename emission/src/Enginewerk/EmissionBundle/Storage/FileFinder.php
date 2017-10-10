<?php
namespace Enginewerk\EmissionBundle\Storage;

use Enginewerk\EmissionBundle\Entity\File as FileEntity;
use Enginewerk\EmissionBundle\Repository\FileBlockRepositoryInterface;
use Enginewerk\EmissionBundle\Repository\FileRepositoryInterface;
use Enginewerk\EmissionBundle\Storage\Model\File;
use Enginewerk\EmissionBundle\Storage\Model\FilePart;
use Enginewerk\EmissionBundle\Storage\Model\FilePartCollection;

final class FileFinder implements FileFinderInterface
{
    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var FileBlockRepositoryInterface */
    private $fileBlockRepository;

    /**
     * @param FileRepositoryInterface $fileRepository
     * @param FileBlockRepositoryInterface $fileBlockRepository
     */
    public function __construct(FileRepositoryInterface $fileRepository, FileBlockRepositoryInterface $fileBlockRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->fileBlockRepository = $fileBlockRepository;
    }

    /**
     * @inheritdoc
     */
    public function getFileByPublicIdentifier($publicIdentifier)
    {
        $fileEntity = $this->fileRepository->findByPublicIdentifier($publicIdentifier);

        if (null === $fileEntity) {
            throw new FileNotFoundException($publicIdentifier);
        }

        return new File(
            $fileEntity->getPublicIdentifier(),
            $fileEntity->getName(),
            $fileEntity->getSize(),
            $fileEntity->getType(),
            $this->findFileParts($fileEntity)
            );
    }

    /**
     * @inheritdoc
     */
    public function findFile($fileName, $fileChecksum, $fileSize)
    {
        $fileEntity = $this->fileRepository->findOneByNameAndChecksumAndSize($fileName, $fileChecksum, (int) $fileSize);

        if (null !== $fileEntity) {
            return new File(
                $fileEntity->getPublicIdentifier(),
                $fileEntity->getName(),
                $fileEntity->getSize(),
                $fileEntity->getType(),
                $this->findFileParts($fileEntity)
            );
        }

        return null;
    }

    /**
     * @param FileEntity $fileEntity
     *
     * @return FilePartCollection
     */
    private function findFileParts(FileEntity $fileEntity)
    {
        $filePartCollection = new FilePartCollection();
        $fileBlocks = $this->fileBlockRepository->findByFileId($fileEntity->getId());

        foreach ($fileBlocks as $fileBlock) {
            $filePartCollection->add(new FilePart(
                $fileBlock->getFileHash(),
                $fileBlock->getRangeStart(),
                $fileBlock->getRangeEnd()
            ));
        }

        return $filePartCollection;
    }
}
