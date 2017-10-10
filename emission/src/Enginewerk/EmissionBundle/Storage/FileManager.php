<?php
namespace Enginewerk\EmissionBundle\Storage;

use Enginewerk\ApplicationBundle\Repository\NoResultException;
use Enginewerk\EmissionBundle\Entity\File as FileEntity;
use Enginewerk\EmissionBundle\Entity\FileBlock;
use Enginewerk\EmissionBundle\Repository\FileBlockRepositoryInterface;
use Enginewerk\EmissionBundle\Repository\FileRepositoryInterface;
use Enginewerk\FSBundle\Service\BinaryStorageInterface;
use Enginewerk\UserBundle\Repository\UserFinderInterface;

final class FileManager implements FileCreationInterface
{
    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var FileBlockRepositoryInterface */
    private $fileBlockRepository;

    /** @var UserFinderInterface */
    private $userRepository;

    /** @var BinaryStorageInterface */
    private $binaryBlockStorage;

    /**
     * @param FileRepositoryInterface $fileRepository
     * @param FileBlockRepositoryInterface $fileBlockRepository
     * @param UserFinderInterface $userRepository
     * @param BinaryStorageInterface $binaryBlockStorage
     */
    public function __construct(
        FileRepositoryInterface $fileRepository,
        FileBlockRepositoryInterface $fileBlockRepository,
        UserFinderInterface $userRepository,
        BinaryStorageInterface $binaryBlockStorage
    ) {
        $this->fileRepository = $fileRepository;
        $this->fileBlockRepository = $fileBlockRepository;
        $this->userRepository = $userRepository;
        $this->binaryBlockStorage = $binaryBlockStorage;
    }

    /**
     * @param string $publicIdentifier
     *
     * @throws FileNotFoundException
     */
    public function delete($publicIdentifier)
    {
        $file = $this->getByPublicIdentifier($publicIdentifier);

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
     * @param string $replacePublicIdentifier
     * @param string $replacementPublicIdentifier
     *
     * @throws FileNotFoundException
     * @throws UserPermissionException
     */
    public function replace($replacePublicIdentifier, $replacementPublicIdentifier)
    {
        $replaceFile = $this->getByPublicIdentifier($replacePublicIdentifier);
        $replacementFile = $this->getByPublicIdentifier($replacementPublicIdentifier);

        if ($replaceFile->getUser()->getId() === $replacementFile->getUser()->getId()) {
            $replacementFile->setPublicIdentifier($replaceFile->getPublicIdentifier());
            $this->fileRepository->persist($replacementFile);

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
     * @param string $publicIdentifier
     * @param \DateTimeInterface $expirationDate
     *
     * @throws FileNotFoundException
     */
    public function alterExpirationDate($publicIdentifier, \DateTimeInterface $expirationDate)
    {
        $file = $this->getByPublicIdentifier($publicIdentifier);
        $file->setExpirationDate(new \DateTime($expirationDate->getTimestamp()));
        $this->fileRepository->persist($file);
    }

    /**
     * @inheritdoc
     */
    public function createFile($fileName, $fileChecksum, $fileSize, $userIdentifier, $mimeType)
    {
        $user = $this->userRepository->getByEmail($userIdentifier);
        $file = new FileEntity();

        $file->setName($fileName);
        $file->setChecksum($fileChecksum);
        $file->setSize($fileSize);
        $file->setType($mimeType);
        $file->setUser($user);
        $file->setComplete(false);

        $this->fileRepository->persist($file);

        return $file->getPublicIdentifier();
    }

    /**
     * @inheritdoc
     */
    public function createFilePart($publicIdentifier, $filePartHash, $size, $rangeStart, $rangeEnd)
    {
        $file = $this->getByPublicIdentifier($publicIdentifier);
        $fileBlock = new FileBlock();

        $fileBlock->setFile($file);
        $fileBlock->setFileHash($filePartHash);
        $fileBlock->setSize($size);
        $fileBlock->setRangeStart($rangeStart);
        $fileBlock->setRangeEnd($rangeEnd);

        $this->fileBlockRepository->persist($fileBlock);
    }

    /**
     * @inheritdoc
     */
    public function setFileAsComplete($publicIdentifier)
    {
        try {
            $file = $this->fileRepository->getByPublicIdentifier($publicIdentifier);
        } catch (NoResultException $exception) {
            throw new FileNotFoundException(sprintf('File identified by "%s" not found', $publicIdentifier));
        }

        $file->setComplete(true);
        $this->fileRepository->persist($file);
    }

    /**
     * @param string $publicIdentifier
     *
     * @throws FileNotFoundException
     *
     * @return FileEntity
     */
    private function getByPublicIdentifier($publicIdentifier)
    {
        try {
            return $this->fileRepository->getByPublicIdentifier($publicIdentifier);
        } catch (NoResultException $noResultException) {
            throw new FileNotFoundException(sprintf('File with key "%s" not found.', $publicIdentifier));
        }
    }
}
