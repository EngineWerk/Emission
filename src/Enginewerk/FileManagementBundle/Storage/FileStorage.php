<?php
namespace Enginewerk\FileManagementBundle\Storage;

use DateTimeInterface;
use Enginewerk\ApplicationBundle\Logger\HasLoggerTrait;
use Enginewerk\ApplicationBundle\Repository\InvalidEntityException;
use Enginewerk\ApplicationBundle\Repository\OptimisticLockException;
use Enginewerk\FileManagementBundle\Entity\FileBlock;
use Enginewerk\FileManagementBundle\Model\File as FileModel;
use Enginewerk\FileManagementBundle\Model\FileCollection;
use Enginewerk\FileManagementBundle\Service\FileReadServiceInterface;
use Enginewerk\FileManagementBundle\Service\FileWriteServiceInterface;
use Enginewerk\FSBundle\Service\BinaryStorageServiceInterface;

final class FileStorage
{
    use HasLoggerTrait;

    /** @var  BinaryStorageServiceInterface */
    private $binaryBlockStorage;

    /** @var  FileReadServiceInterface */
    private $fileReadService;

    /** @var  FileWriteServiceInterface */
    private $fileWriteService;

    /**
     * @param BinaryStorageServiceInterface $binaryBlockStorage
     * @param FileReadServiceInterface $fileReadService
     * @param FileWriteServiceInterface $fileWriteService
     */
    public function __construct(
        BinaryStorageServiceInterface $binaryBlockStorage,
        FileReadServiceInterface $fileReadService,
        FileWriteServiceInterface $fileWriteService
    ) {
        $this->binaryBlockStorage = $binaryBlockStorage;
        $this->fileReadService = $fileReadService;
        $this->fileWriteService = $fileWriteService;
    }

    /**
     * @return FileCollection
     */
    public function findAllFiles()
    {
        return $this->fileReadService->findAllFiles();
    }

    /**
     * @param string $shortIdentifier
     *
     * @return FileModel
     */
    public function findByShortIdentifier($shortIdentifier)
    {
        return $this->fileReadService->findFileByShortIdentifier($shortIdentifier);
    }

    /**
     * @param string $shortFileIdentifier
     *
     * @throws FileNotFoundException
     * @throws InvalidFileIdentifierException
     */
    public function deleteFile($shortFileIdentifier)
    {
        $file = $this->fileReadService->getByShortFileIdentifier($shortFileIdentifier);

        /** @var FileBlock $fileBlock */
        foreach ($file->getFileBlocks() as $fileBlock) {
            $usedBlocks = $this->fileReadService->getUsedBlocksNumber($fileBlock->getFileHash());

            $binaryBlockKey = $fileBlock->getFileHash();
            $this->fileWriteService->removeFileBlock($fileBlock);

            if (null === $usedBlocks || 1 === $usedBlocks) {
                $this->binaryBlockStorage->delete($binaryBlockKey);
            }
        }

        $this->fileWriteService->removeFile($file);
    }

    /**
     * @param string $replaceShortFileIdentifier
     * @param string $replacementShortFileIdentifier
     *
     * @throws UserPermissionException
     * @throws InvalidEntityException
     * @throws OptimisticLockException
     * @throws InvalidFileIdentifierException
     * @throws FileNotFoundException
     */
    public function replace($replaceShortFileIdentifier, $replacementShortFileIdentifier)
    {
        $replaceFile = $this->fileReadService->getByShortFileIdentifier($replaceShortFileIdentifier);
        $replacementFile = $this->fileReadService->getByShortFileIdentifier($replacementShortFileIdentifier);

        if ($replaceFile->getUser()->getId() === $replacementFile->getUser()->getId()) {
            $replacementFile->setFileId($replaceFile->getFileId());
            $this->fileWriteService->persistFile($replacementFile);

            $replaceFileKey = $replaceFile->getFileHash();
            $this->binaryBlockStorage->delete($replaceFileKey);
            $this->fileWriteService->removeFile($replaceFile);
        } else {
            $this->getLogger()->error(
                sprintf(
                    'Only owner "%s" can replace file.',
                    $replaceFile->getUser()->getUsername()
                )
            );

            throw new UserPermissionException(sprintf(
                'Only owner "%s" can replace file.',
                $replaceFile->getUser()->getUsername()
            ));
        }
    }

    /**
     * @param string $fileShortIdentifier
     * @param DateTimeInterface|null $expirationDate
     *
     * @throws FileNotFoundException
     * @throws InvalidFileIdentifierException
     */
    public function setFileExpirationDate($fileShortIdentifier, DateTimeInterface $expirationDate = null)
    {
        $file = $this->fileReadService->findByShortIdentifier($fileShortIdentifier);
        $file->setExpirationDate(new \DateTime($expirationDate->getTimestamp()));
        $this->fileWriteService->persistFile($file);
    }
}
