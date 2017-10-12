<?php
namespace Enginewerk\ResumableBundle\Service;

use Enginewerk\ApplicationBundle\Response\ServiceResponse;
use Enginewerk\ApplicationBundle\Response\WebApplicationResponse;
use Enginewerk\Common\Logger\HasLoggerTrait;
use Enginewerk\Common\Uuid\UuidGeneratorInterface;
use Enginewerk\EmissionBundle\Service\FileViewFinderInterface;
use Enginewerk\EmissionBundle\Storage\FileCreationInterface;
use Enginewerk\EmissionBundle\Storage\FileFinderInterface;
use Enginewerk\EmissionBundle\Storage\Model\File;
use Enginewerk\EmissionBundle\Storage\Model\FilePart;
use Enginewerk\EmissionBundle\Storage\Model\FilePartCollection;
use Enginewerk\FSBundle\Service\BinaryStorageInterface;
use Enginewerk\ResumableBundle\FileUpload\FileRequest;
use Enginewerk\ResumableBundle\FileUpload\ResponseFactoryInterface;
use Enginewerk\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ResumableFileUploadService
{
    use HasLoggerTrait;

    /** @var FileFinderInterface */
    private $fileFinder;

    /** @var FileCreationInterface */
    private $fileManager;

    /** @var FileViewFinderInterface */
    private $fileViewFinder;

    /** @var ResponseFactoryInterface */
    private $responseFactory;

    /** @var BinaryStorageInterface */
    private $binaryStorageService;

    /** @var UuidGeneratorInterface */
    private $identifierGenerator;

    /**
     * @param FileFinderInterface $fileFinder
     * @param FileCreationInterface $fileManager
     * @param FileViewFinderInterface $fileViewFinder
     * @param ResponseFactoryInterface $responseFactory
     * @param BinaryStorageInterface $binaryStorageService
     * @param UuidGeneratorInterface $identifierGenerator
     */
    public function __construct(
        FileFinderInterface $fileFinder,
        FileCreationInterface $fileManager,
        FileViewFinderInterface $fileViewFinder,
        ResponseFactoryInterface $responseFactory,
        BinaryStorageInterface $binaryStorageService,
        UuidGeneratorInterface $identifierGenerator
    ) {
        $this->fileFinder = $fileFinder;
        $this->fileManager = $fileManager;
        $this->fileViewFinder = $fileViewFinder;
        $this->responseFactory = $responseFactory;
        $this->binaryStorageService = $binaryStorageService;
        $this->identifierGenerator = $identifierGenerator;
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
        $this->getLogger()->info(
            'Receiving new upload request',
            [
                'ResumableFilename' => $resumableRequest->getResumableFilename(),
                'ResumableIdentifier' => $resumableRequest->getResumableIdentifier(),
                'ResumableChunkNumber' => $resumableRequest->getResumableChunkNumber(),
                'ResumableTotalChunksNumber' =>$resumableRequest->getResumableTotalChunks(),
            ]
        );

        if ($uploadedFile->getError()) {
            $this->getLogger()->error(
                'Uploaded File error',
                [
                    'ErrorMessage' => $uploadedFile->getErrorMessage(),
                    'ErrorNumber' => $uploadedFile->getError(),
                ]
            );
        }

        $file = $this->fileFinder->findFile(
            $resumableRequest->getResumableFilename(),
            $resumableRequest->getResumableIdentifier(),
            $resumableRequest->getResumableTotalSize()
        );

        if (null === $file) {
            $this->getLogger()->info('File is new');
            $publicIdentifier = $this->fileManager->createFile(
                $resumableRequest->getResumableFilename(),
                $resumableRequest->getResumableIdentifier(),
                $resumableRequest->getResumableTotalSize(),
                $user->getEmail(),
                $uploadedFile->getMimeType()
            );

            $file = $this->fileFinder->getFileByPublicIdentifier($publicIdentifier);
        } else {
            $this->getLogger()->info('File exists');
            $publicIdentifier = $file->getPublicIdentifier();
        }

        $filePart = $this->findFilePart(
            $file->getFilePartCollection(),
            $resumableRequest->getResumableCurrentStartByte(),
            $resumableRequest->getResumableCurrentEndByte()
        );

        $storedFilePartSize = 0;
        if (null === $filePart) {
            $storedFilePartSize = $this->storeFilePart($uploadedFile, $resumableRequest, $publicIdentifier);
        }

        $this->getLogger()->debug(
            'Uploaded part',
            [
                'FileSize' => $file->getSize(),
                'FilePartExists' => null !== $filePart,
                'StoredBinarySize' => $storedFilePartSize,
                'SumPartsSize' => $this->sumPartsSize($file->getFilePartCollection()),
                'TotalPartsSize' => $this->sumPartsSize($file->getFilePartCollection()) + $storedFilePartSize,
            ]
        );

        if ($this->currentFilePartCompletesFile($file, $storedFilePartSize)) {
            $this->getLogger()->info('Uploaded File complete (all parts received)');
            $this->fileManager->setFileAsComplete($publicIdentifier);
        }

        $fileView = $this->fileViewFinder->getByPublicIdentifier($publicIdentifier);

        if ($this->currentFilePartCompletesFile($file, $storedFilePartSize)) {
            $fileResponse = $this->responseFactory->createCompleteFileResponse($fileView);
        } else {
            $fileResponse = $this->responseFactory->createIncompleteFileResponse($fileView);
        }

        $responseCode = 200;
        $applicationResponse = new WebApplicationResponse();
        $applicationResponse->success();
        $applicationResponse->data($fileResponse->toArray());

        return new ServiceResponse($responseCode, $applicationResponse->toArray());
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
        $response = new WebApplicationResponse();

        $file = $this->fileFinder->findFile($fileName, $fileChecksum, (int) $fileSize);

        if ($file) {
            $filePart = $this->findFilePart(
                $file->getFilePartCollection(),
                (int) $chunkRangeStart,
                (int) $chunkRangeEnd
            );
            if ($filePart) {
                $response->success('Block found');
                $responseCode = 200;
            } else {
                $response->success('Block not found');
                $responseCode = 306;
            }
        } else {
            $response->error(sprintf('File "%s" not found', $fileName));
            $responseCode = 306;
        }

        return new ServiceResponse($responseCode, $response->toArray());
    }

    /**
     * @param FilePartCollection $filePartCollection
     * @param int $startByte
     * @param int $endByte
     *
     * @return FilePart|null
     */
    private function findFilePart(FilePartCollection $filePartCollection, $startByte, $endByte)
    {
        /** @var FilePart $filePart */
        foreach ($filePartCollection as $filePart) {
            if ($filePart->getRangeStart() === $startByte && $filePart->getRangeEnd() === $endByte) {
                return $filePart;
            }
        }

        return null;
    }

    /**
     * @param FilePartCollection $filePartCollection
     *
     * @return int
     */
    private function sumPartsSize(FilePartCollection $filePartCollection)
    {
        $size = 0;

        /** @var FilePart $filePart */
        foreach ($filePartCollection as $filePart) {
            $size += $filePart->getRangeEnd() - $filePart->getRangeStart();
        }

        return $size;
    }

    /**
     * @param File $file
     * @param int $storedFilePartSize
     *
     * @return bool
     */
    private function currentFilePartCompletesFile(File $file, $storedFilePartSize)
    {
        return $this->sumPartsSize($file->getFilePartCollection()) + $storedFilePartSize === $file->getSize();
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param FileRequest $resumableRequest
     * @param string $publicIdentifier
     *
     * @return int
     */
    private function storeFilePart(UploadedFile $uploadedFile, FileRequest $resumableRequest, $publicIdentifier)
    {
        $this->getLogger()->info('File part is new');
        $binaryFileIdentifier = $this->identifierGenerator->generate();
        // Store binary data
        $storedFilePartSize = $this->binaryStorageService->store($uploadedFile, $binaryFileIdentifier);
        // Store meta of binary data
        $this->fileManager->createFilePart(
            $publicIdentifier,
            $binaryFileIdentifier,
            $storedFilePartSize,
            $resumableRequest->getResumableCurrentStartByte(),
            $resumableRequest->getResumableCurrentEndByte()
        );

        return $storedFilePartSize;
    }
}
