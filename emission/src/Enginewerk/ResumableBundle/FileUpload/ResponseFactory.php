<?php
namespace Enginewerk\ResumableBundle\FileUpload;

use Enginewerk\EmissionBundle\Presentation\Model\FileView;
use Enginewerk\ResumableBundle\FileUpload\Response\CompleteFileResponse;
use Enginewerk\ResumableBundle\FileUpload\Response\IncompleteFileResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ResponseFactory implements ResponseFactoryInterface
{
    /** @var UrlGeneratorInterface */
    private $routeGenerator;

    /**
     * @param UrlGeneratorInterface $routeGenerator
     */
    public function __construct(UrlGeneratorInterface $routeGenerator)
    {
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * @param FileView $fileView
     *
     * @return IncompleteFileResponse
     */
    public function createIncompleteFileResponse(FileView $fileView)
    {
        return new IncompleteFileResponse(
            $fileView->getId(),
            $fileView->getFileId(),
            $fileView->getName(),
            $fileView->getType(),
            $fileView->getSizeBytes()
        );
    }

    /**
     * @param FileView $fileView
     *
     * @return CompleteFileResponse
     */
    public function createCompleteFileResponse(FileView $fileView)
    {
        return new CompleteFileResponse(
            $fileView->getId(),
            $fileView->getFileId(),
            $fileView->getName(),
            $fileView->getType(),
            $fileView->getSizeBytes(),
            $fileView->getExpirationDate(),
            $fileView->getUpdatedAt(),
            $fileView->getCreatedAt(),
            $fileView->getUserName(),
            $this->routeGenerator->generate(
                'show_file',
                ['file' => $fileView->getFileId()],
                true
            ),
            $this->routeGenerator->generate(
                'download_file',
                ['fileShortIdentifier' => $fileView->getFileId()],
                true
            ),
            $this->routeGenerator->generate(
                'open_file',
                ['fileShortIdentifier' => $fileView->getFileId()],
                true
            ),
            $this->routeGenerator->generate(
                'delete_file',
                ['file' => $fileView->getFileId()],
                true
            )
        );
    }
}
