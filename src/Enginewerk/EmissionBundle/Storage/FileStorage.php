<?php
namespace Enginewerk\EmissionBundle\Storage;

use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\Entity\FileBlock;
use Enginewerk\EmissionBundle\FileResponse\ChunkedFile;
use Enginewerk\EmissionBundle\Repository\FileBlockRepositoryInterface;
use Enginewerk\EmissionBundle\Repository\FileRepositoryInterface;
use Enginewerk\FSBundle\Service\StorageService;

final class FileStorage
{
    /** @var  FileRepositoryInterface */
    private $fileRepository;

    /** @var  FileBlockRepositoryInterface */
    private $fileBlockRepository;

    /** @var  StorageService */
    private $binaryBlockStorage;

    /**
     * @param FileRepositoryInterface $fileRepository
     * @param FileBlockRepositoryInterface $fileBlockRepository
     * @param StorageService $binaryBlockStorage
     */
    public function __construct(
        FileRepositoryInterface $fileRepository,
        FileBlockRepositoryInterface $fileBlockRepository,
        StorageService $binaryBlockStorage
    ) {
        $this->fileRepository = $fileRepository;
        $this->fileBlockRepository = $fileBlockRepository;
        $this->binaryBlockStorage = $binaryBlockStorage;
    }

    /**
     * @param string $shortFileIdentifier
     */
    public function delete($shortFileIdentifier)
    {
        $file = $this->getByShortFileIdentifier($shortFileIdentifier);

        /** @var FileBlock $fileBlock */
        foreach ($file->getFileBlocks() as $fileBlock) {
            $usedBlocks = $this->fileBlockRepository->getUsedBlocksNumber($fileBlock->getFileHash());

            $binaryBlockKey = $fileBlock->getFileHash();
            $this->fileBlockRepository->remove($fileBlock);

            if (null === $usedBlocks || 1 === $usedBlocks) {
                $this->binaryBlockStorage->delete($binaryBlockKey);
            }
        }

        $this->fileRepository->remove($file);
    }

    /**
     * @param string $shortFileIdentifier
     *
     * @throws FileNotFoundException
     *
     * @return File
     *
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
     * @param string $fileShortIdentifier
     *
     * @return ChunkedFile
     */
    public function getFileForDownload($fileShortIdentifier)
    {
        $file = $this->findByShortIdentifier($fileShortIdentifier);

        $fileBlocks = $this->fileBlockRepository->findByFileId($file->getId());

        $binaryBlocks = [];
        foreach ($fileBlocks as $fileBlock) {
            $binaryBlocks[] = $this->binaryBlockStorage->get($fileBlock->getFileHash());
        }

        return new ChunkedFile($binaryBlocks);
    }

    /**
     * @param string $identifier
     *
     * @throws \RuntimeException
     *
     * @return File|null
     *
     */
    public function findByShortIdentifier($identifier)
    {
        if (mb_strlen($identifier) === 0) {
            throw new \RuntimeException('File short identifier cannot be empty');
        }

        return $this->fileRepository->findOneByShortIdentifier($identifier);
    }

    /**
     * @param string $replace
     * @param string $replacement
     *
     * @throws \Exception
     */
    public function replace($replace, $replacement)
    {
        $replaceFile = $this->getByShortFileIdentifier($replace);
        /* @var $replaceFile \Enginewerk\EmissionBundle\Entity\File */

        $replacementFile = $this->getByShortFileIdentifier($replacement);
        /* @var $replacementFile \Enginewerk\EmissionBundle\Entity\File */

        if ($replaceFile->getUser()->getId() === $replacementFile->getUser()->getId()) {
            $replacementFile->setFileId($replaceFile->getFileId());
            $this->fileRepository->update($replacementFile);

            $replaceFileKey = $replaceFile->getFileHash();
            $this->binaryBlockStorage->delete($replaceFileKey);
            $this->fileRepository->remove($replaceFile);
        } else {
            throw new UserPermissionException(sprintf(
                'Only owner "%s" can replace file.',
                $replaceFile->getUser()->getUsername()
            ));
        }
    }

    /**
     * @param string $shortFileIdentifier
     * @param \DateTimeInterface $expirationDate
     */
    public function alterExpirationDate($shortFileIdentifier, \DateTimeInterface $expirationDate)
    {
        $file = $this->getByShortFileIdentifier($shortFileIdentifier);

        $file->setExpirationDate(new \DateTime($expirationDate->getTimestamp()));

        $this->fileRepository->update($file);
    }
}
