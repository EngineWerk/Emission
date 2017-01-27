<?php
namespace Enginewerk\ResumableBundle\Service;

use Enginewerk\ApplicationBundle\Response\ErrorResponse;
use Enginewerk\ApplicationBundle\Response\ServiceResponse;
use Enginewerk\ApplicationBundle\Response\SuccessResponse;
use Enginewerk\EmissionBundle\Service\FileViewServiceInterface;
use Enginewerk\FileManagementBundle\Service\FileReadServiceInterface;
use Enginewerk\FileManagementBundle\Service\FileWriteServiceInterface;
use Enginewerk\FSBundle\Service\BinaryStorageServiceInterface;
use Enginewerk\ResumableBundle\Request\FileRequest;
use Enginewerk\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ResumableFileUploadService
{
    /** @var  BinaryStorageServiceInterface */
    protected $binaryStorageService;

    /** @var  FileViewServiceInterface */
    protected $fileViewService;

    /** @var  FileWriteServiceInterface */
    protected $fileWriteService;

    /** @var  FileReadServiceInterface */
    protected $fileReadService;

    /**
     * @param BinaryStorageServiceInterface $binaryStorageService
     * @param FileViewServiceInterface $fileViewService
     * @param FileWriteServiceInterface $fileWriteService
     * @param FileReadServiceInterface $fileReadService
     */
    public function __construct(
        BinaryStorageServiceInterface $binaryStorageService,
        FileViewServiceInterface $fileViewService,
        FileWriteServiceInterface $fileWriteService,
        FileReadServiceInterface $fileReadService
    ) {
        $this->binaryStorageService = $binaryStorageService;
        $this->fileViewService = $fileViewService;
        $this->fileWriteService = $fileWriteService;
        $this->fileReadService = $fileReadService;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param FileRequest $resumableRequest
     * @param User $user
     *
     * @return ServiceResponse
     */
    public function uploadFromRequest(UploadedFile $uploadedFile, FileRequest $resumableRequest, User $user)
    {
        $fileEntity = $this->fileReadService->findFile(
            $resumableRequest->getResumableFilename(),
            $resumableRequest->getResumableIdentifier(),
            $resumableRequest->getResumableTotalSize()
        );

        if (null === $fileEntity) {
            $fileEntity = $this->fileWriteService->createFile(
                $resumableRequest->getResumableFilename(),
                $resumableRequest->getResumableIdentifier(),
                $resumableRequest->getResumableTotalSize(),
                $user,
                $uploadedFile->getMimeType()
            );
        }

        $fileBlockInStorage = $this->fileReadService->findFileBlock(
            $fileEntity->getId(),
            $resumableRequest->getResumableCurrentStartByte(),
            $resumableRequest->getResumableCurrentEndByte()
        );

        if (null === $fileBlockInStorage) {
            $binaryFileIdentifier = sha1(microtime() . $uploadedFile->getPathname());
            // Store binary data
            $size = $this->binaryStorageService->store($uploadedFile, $binaryFileIdentifier);

            // Store meta of binary data
            $this->fileWriteService->createFileBlock(
                $fileEntity,
                $binaryFileIdentifier,
                $size,
                $resumableRequest->getResumableCurrentStartByte(),
                $resumableRequest->getResumableCurrentEndByte()
            );
        }

        if ($this->fileReadService->getTotalSize($fileEntity->getId()) === (int) $fileEntity->getSize()) {
            $this->fileWriteService->setFileAsComplete($fileEntity);
            $responseData = $this->fileViewService->createResponseForCompleteFile($fileEntity);
        } else {
            $responseData = $this->fileViewService->createResponseForIncompleteFile($fileEntity);
        }

        return new ServiceResponse(
            Response::HTTP_OK,
            (new SuccessResponse(
                SuccessResponse::DEFAULT_MESSAGE,
                $responseData
            ))->toArray()
        );
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
        $file = $this->fileReadService->findOneByNameAndChecksumAndSize($fileName, $fileChecksum, $fileSize);

        if ($file) {
            if ($this->fileReadService->hasFileFileBlock($file->getId(), $chunkRangeStart, $chunkRangeEnd)) {
                return new ServiceResponse(
                    Response::HTTP_OK,
                    (new SuccessResponse(
                        'Block found'
                    ))->toArray()
                );
            } else {
                return new ServiceResponse(
                    Response::HTTP_RESERVED,
                    (new ErrorResponse(
                        'Block not found'
                    ))->toArray()
                );
            }
        } else {
            return new ServiceResponse(
                Response::HTTP_RESERVED,
                (new ErrorResponse(
                    sprintf('File "%s" not found', $fileName)
                ))->toArray()
            );
        }
    }
}
