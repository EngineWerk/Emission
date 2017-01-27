<?php
namespace Enginewerk\FileManagementBundle\Service;

use Enginewerk\FileManagementBundle\Entity\File;
use Enginewerk\FileManagementBundle\Entity\FileBlock;
use Enginewerk\FileManagementBundle\Repository\FileBlockRepositoryInterface;
use Enginewerk\FileManagementBundle\Repository\FileRepositoryInterface;
use Enginewerk\UserBundle\Entity\User;

class FileWriteService implements FileWriteServiceInterface
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
    public function createFile($fileName, $fileChecksum, $fileSize, User $user, $mimeType)
    {
        $file = new File();

        $file->setName($fileName);
        $file->setChecksum($fileChecksum);
        $file->setSize($fileSize);
        $file->setType($mimeType);
        $file->setUser($user);
        $file->setComplete(false);

        $this->fileRepository->persist($file);

        return $file;
    }

    /**
     * @inheritdoc
     */
    public function setFileAsComplete(File $file)
    {
        $file->setComplete(true);

        $this->fileRepository->persist($file);
    }

    /**
     * @inheritdoc
     */
    public function createFileBlock(File $file, $fileHash, $size, $rangeStart, $rangeEnd)
    {
        $fileBlock = new FileBlock();

        $fileBlock->setFile($file);
        $fileBlock->setFileHash($fileHash);
        $fileBlock->setSize($size);
        $fileBlock->setRangeStart($rangeStart);
        $fileBlock->setRangeEnd($rangeEnd);

        $this->fileBlockRepository->persist($fileBlock);

        return $fileBlock;
    }

    /**
     * @inheritdoc
     */
    public function removeFile(File $file)
    {
        $this->fileRepository->remove($file);
    }

    /**
     * @inheritdoc
     */
    public function removeFileBlock(FileBlock $fileBlock)
    {
        $this->fileBlockRepository->remove($fileBlock);
    }

    /**
     * @inheritdoc
     */
    public function persistFile(File $file)
    {
        $this->fileRepository->persist($file);
    }
}
