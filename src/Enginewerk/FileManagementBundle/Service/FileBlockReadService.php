<?php
namespace Enginewerk\FileManagementBundle\Service;

use Enginewerk\ApplicationBundle\Logger\HasLoggerTrait;
use Enginewerk\FileManagementBundle\Entity\File;
use Enginewerk\FileManagementBundle\FileResponse\BinaryBlockCollection;
use Enginewerk\FileManagementBundle\Storage\InvalidFileIdentifierException;
use Enginewerk\FSBundle\Service\BinaryStorageServiceInterface;

class FileBlockReadService implements FileBlockReadServiceInterface
{
    use HasLoggerTrait;

    /** @var  FileReadServiceInterface */
    protected $fileReadService;

    /** @var  BinaryStorageServiceInterface */
    protected $binaryBlockStorage;

    /**
     * @param FileReadServiceInterface $fileReadService
     * @param BinaryStorageServiceInterface $binaryBlockStorage
     */
    public function __construct(
        FileReadServiceInterface $fileReadService,
        BinaryStorageServiceInterface $binaryBlockStorage
    ) {
        $this->fileReadService = $fileReadService;
        $this->binaryBlockStorage = $binaryBlockStorage;
    }

    /**
     * @inheritdoc
     */
    public function getFileBlockCollection($fileShortIdentifier)
    {
        return $this->getBlockCollection(
            $this->fileReadService->getByShortFileIdentifier($fileShortIdentifier)
        );
    }

    /**
     * @param File $file
     *
     * @throws InvalidFileIdentifierException
     *
     * @return BinaryBlockCollection
     *
     */
    protected function getBlockCollection(File $file)
    {
        $fileBlocks = $this->fileReadService->findBlocksByFileId($file->getId());

        $binaryBlocks = [];
        foreach ($fileBlocks as $fileBlock) {
            $binaryBlocks[] = $this->binaryBlockStorage->get($fileBlock->getFileHash());
        }

        return new BinaryBlockCollection($binaryBlocks);
    }
}
