<?php
namespace Enginewerk\EmissionBundle\Storage;

use DateTimeInterface;
use Enginewerk\ApplicationBundle\Logger\HasLoggerTrait;
use Enginewerk\ApplicationBundle\Response\ApplicationResponse;
use Enginewerk\ApplicationBundle\Response\ServiceResponse;
use Enginewerk\EmissionBundle\Entity\FileBlock;
use Enginewerk\EmissionBundle\Service\FileReadServiceInterface;
use Enginewerk\EmissionBundle\Service\FileWriteServiceInterface;
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
     * @param string $shortFileIdentifier
     *
     * @throws FileNotFoundException
     * @throws InvalidFileIdentifierException
     */
    protected function delete($shortFileIdentifier)
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
     * @param string $shortFileIdentifier
     *
     * @return ServiceResponse
     */
    public function deleteFile($shortFileIdentifier)
    {
        $applicationResponse = new ApplicationResponse();

        try {
            $this->delete($shortFileIdentifier);
            $applicationResponse->success();
        } catch (\Exception $ex) {
            $applicationResponse->error(sprintf(
                'Can`t delete File identified by shortFileIdentifier "%s"',
                $shortFileIdentifier
            ));
            $this->getLogger()
                ->error(
                sprintf(
                    'Can`t delete File identified by shortFileIdentifier "%s". %s',
                    $shortFileIdentifier,
                    $ex->getMessage())
            );
        }

        return new ServiceResponse(200, $applicationResponse->toArray());
    }

    /**
     * @param string $replaceShortFileIdentifier
     * @param string $replacementShortFileIdentifier
     *
     * @return ServiceResponse
     */
    public function replace($replaceShortFileIdentifier, $replacementShortFileIdentifier)
    {
        $appResponse = new ApplicationResponse();

        $replaceFile = $this->fileReadService->getByShortFileIdentifier($replaceShortFileIdentifier);
        $replacementFile = $this->fileReadService->getByShortFileIdentifier($replacementShortFileIdentifier);

        if ($replaceFile->getUser()->getId() === $replacementFile->getUser()->getId()) {
            try {
                $replacementFile->setFileId($replaceFile->getFileId());
                $this->fileWriteService->persistFile($replacementFile);

                $replaceFileKey = $replaceFile->getFileHash();
                $this->binaryBlockStorage->delete($replaceFileKey);
                $this->fileWriteService->removeFile($replaceFile);

                $appResponse->success('File replaced.');
                $responseCode = 200;
            } catch (\Exception $exception) {
                $this->getLogger()->error($exception->getMessage());
                $appResponse->error('Can\'t replace file');
                $responseCode = 403;
            }
        } else {
            $this->getLogger()->error(
                sprintf(
                    'Only owner "%s" can replace file.',
                    $replaceFile->getUser()->getUsername()
                )
            );

            $appResponse->error(sprintf(
                'Only owner "%s" can replace file.',
                $replaceFile->getUser()->getUsername()
            ));
            $responseCode = 403;
        }

        return new ServiceResponse($responseCode, $appResponse->toArray());
    }

    /**
     * @param string $fileShortIdentifier
     * @param DateTimeInterface|null $expirationDate
     *
     * @return ServiceResponse
     */
    public function setFileExpirationDate($fileShortIdentifier, DateTimeInterface $expirationDate = null)
    {
        $applicationResponse = new ApplicationResponse();

        try {
            $file = $this->fileReadService->findByShortIdentifier($fileShortIdentifier);
            $file->setExpirationDate(new \DateTime($expirationDate->getTimestamp()));
            $this->fileWriteService->persistFile($file);

            $applicationResponse->success();
            $responseCode = 200;
        } catch (InvalidFileIdentifierException $invalidIdentifierException) {
            $this->getLogger()->error($invalidIdentifierException->getMessage());

            $applicationResponse->error(sprintf(
                'File identified by "%s" was not found.',
                $fileShortIdentifier
            ));
            $responseCode = 404;
        } catch (FileNotFoundException $fileNotFoundException) {
            $this->getLogger()->error(sprintf(
                'Can`t change expiration date of File #%s. %s',
                $fileShortIdentifier,
                $fileNotFoundException->getMessage()
            ));

            $applicationResponse->error('Can`t change expiration date');
            $responseCode = 413;
        } catch (\Exception $exception) {
            $this->getLogger()->error($exception->getMessage());
            $applicationResponse->error('Can`t change expiration date');
            $responseCode = 404; // Set proper code
        }

        return new ServiceResponse($responseCode, $applicationResponse->toArray());
    }

    /**
     * @param string|null $createdAfter
     *
     * @return ServiceResponse
     */
    public function getFilesForJsonApi($createdAfter = null)
    {
        $applicationResponse = new ApplicationResponse();

        $applicationResponse->success();
        $applicationResponse->data(
            $this->fileReadService->getFilesForJsonApi(new \DateTime($createdAfter ?: 'now'))
        );

        return new ServiceResponse(200, $applicationResponse->toArray());
    }
}
