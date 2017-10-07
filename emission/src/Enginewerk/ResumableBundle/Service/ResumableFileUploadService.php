<?php
namespace Enginewerk\ResumableBundle\Service;

use Enginewerk\ApplicationBundle\Response\ServiceResponse;
use Enginewerk\ApplicationBundle\Response\WebApplicationResponse;
use Enginewerk\EmissionBundle\Service\FileReadServiceInterface;
use Enginewerk\EmissionBundle\Service\FileViewServiceInterface;
use Enginewerk\EmissionBundle\Service\FileWriteServiceInterface;
use Enginewerk\FSBundle\Service\BinaryStorageService;
use Enginewerk\ResumableBundle\Request\FileRequest;
use Enginewerk\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ResumableFileUploadService
{
    /** @var  BinaryStorageService */
    protected $binaryStorageService;

    /** @var  FileViewServiceInterface */
    protected $fileViewService;

    /** @var  FileWriteServiceInterface */
    protected $fileWriteService;

    /** @var  FileReadServiceInterface */
    protected $fileReadService;

    /**
     * @param BinaryStorageService $binaryStorageService
     * @param FileViewServiceInterface $fileViewService
     * @param FileWriteServiceInterface $fileWriteService
     * @param FileReadServiceInterface $fileReadService
     */
    public function __construct(
        BinaryStorageService $binaryStorageService,
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
        $applicationResponse = new WebApplicationResponse();

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

        $responseCode = 200;
        $applicationResponse->success();
        $applicationResponse->data($responseData);

        return new ServiceResponse($responseCode, $applicationResponse->toArray());
    }
}
