<?php
namespace Enginewerk\EmissionBundle\Service;

use Enginewerk\ApplicationBundle\Logger\HasLoggerTrait;
use Enginewerk\FileManagementBundle\Service\FileBlockReadServiceInterface;
use Enginewerk\FileManagementBundle\Service\FileReadServiceInterface;
use Enginewerk\FileManagementBundle\Storage\FileNotFoundException;
use Enginewerk\FileManagementBundle\Storage\InvalidFileIdentifierException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileStreamService
{
    use HasLoggerTrait;

    /** @var  FileBlockReadServiceInterface */
    protected $fileBlockReadService;

    /** @var  FileReadServiceInterface */
    protected $fileReadService;

    /**
     * @param FileBlockReadServiceInterface $fileBlockReadService
     * @param FileReadServiceInterface $fileReadService
     */
    public function __construct(
        FileBlockReadServiceInterface $fileBlockReadService,
        FileReadServiceInterface $fileReadService
    ) {
        $this->fileBlockReadService = $fileBlockReadService;
        $this->fileReadService = $fileReadService;
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
            $file = $this->fileReadService->getByShortFileIdentifier($fileShortIdentifier);
            $binaryBlockCollection = $this->fileBlockReadService->getFileBlockCollection($fileShortIdentifier);
        } catch (FileNotFoundException $fileNotFoundException) {
            return new NotFoundHttpException($fileNotFoundException->getMessage());
        } catch (InvalidFileIdentifierException $invalidFileIdentifierException) {
            $this->getLogger()->error($invalidFileIdentifierException->getMessage());

            return new Response('File not found', 404);
        }

        $response = new StreamedResponse();

        $response->headers->set('Content-Type', $file->getType());
        $response->headers->set('Content-Length', $file->getSize());
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        if ($returnAsAttachment) {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $file->getName() . '"');
        }

        try {
            $response->setCallback(function () use ($binaryBlockCollection) {
                try {
                    $binaryBlockCollection->read();
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
}
