<?php
namespace Enginewerk\EmissionBundle\Service;

use Enginewerk\ApplicationBundle\Logger\HasLoggerTrait;
use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\FileResponse\FileBinaryBlockCollection;
use Enginewerk\EmissionBundle\Storage\InvalidFileIdentifierException;
use Enginewerk\FSBundle\Service\BinaryStorageServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileStreamService
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
     * @param string $fileShortIdentifier
     * @param bool $returnAsAttachment
     *
     * @return StreamedResponse|Response|NotFoundHttpException
     */
    public function getFileForDownload($fileShortIdentifier, $returnAsAttachment = false)
    {
        try {
            if (null === ($file = $this->fileReadService->findByShortIdentifier($fileShortIdentifier))) {
                return new NotFoundHttpException('File not found');
            }
        } catch (InvalidFileIdentifierException $invalidFileIdentifierException) {
            $this->getLogger()->error($invalidFileIdentifierException->getMessage());

            return new Response('File not found', 404);
        }

        $fileBinaryBlockCollection = $this->getBlockCollection($file);

        $response = new StreamedResponse();

        $response->headers->set('Content-Type', $file->getType());
        $response->headers->set('Content-Length', $file->getSize());
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        if ($returnAsAttachment) {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $file->getName() . '"');
        }

        try {
            $response->setCallback(function () use ($fileBinaryBlockCollection) {
                try {
                    $fileBinaryBlockCollection->read();
                } catch (\RuntimeException $exception) {
                    $this->getLogger()->critical($exception->getMessage());
                }
            });
        } catch (\LogicException $logicException) {
            $this->getLogger()->error($logicException->getMessage());

            return new Response($logicException->getMessage(), 500);
        }

        return $response;
    }

    /**
     * @param File $file
     *
     * @throws InvalidFileIdentifierException
     *
     * @return FileBinaryBlockCollection
     *
     */
    protected function getBlockCollection(File $file)
    {
        $fileBlocks = $this->fileReadService->findBlocksByFileId($file->getId());

        $binaryBlocks = [];
        foreach ($fileBlocks as $fileBlock) {
            $binaryBlocks[] = $this->binaryBlockStorage->get($fileBlock->getFileHash());
        }

        return new FileBinaryBlockCollection($binaryBlocks);
    }
}
