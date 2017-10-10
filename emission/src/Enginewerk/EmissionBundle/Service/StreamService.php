<?php
namespace Enginewerk\EmissionBundle\Service;

use Enginewerk\EmissionBundle\Storage\FileFinderInterface;
use Enginewerk\EmissionBundle\Storage\FileNotFoundException;
use Enginewerk\EmissionBundle\Stream\FileReaderInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class StreamService
{
    /** @var FileFinderInterface */
    private $fileFinder;

    /** @var FileReaderInterface */
    private $fileReader;

    /**
     * @param FileFinderInterface $fileFinder
     * @param FileReaderInterface $fileReader
     */
    public function __construct(FileFinderInterface $fileFinder, FileReaderInterface $fileReader)
    {
        $this->fileFinder = $fileFinder;
        $this->fileReader = $fileReader;
    }

    /**
     * @param $fileShortIdentifier
     * @param bool $streamAsAttachment
     *
     * @throws FileNotFoundException
     *
     * @return StreamedResponse
     */
    public function getStreamResponse($fileShortIdentifier, $streamAsAttachment = false)
    {
        $file = $this->fileFinder->getFileByPublicIdentifier($fileShortIdentifier);

        $response = new StreamedResponse();

        $response->headers->set('Content-Type', $file->getType());
        $response->headers->set('Content-Length', $file->getSize());
        $response->headers->set('Content-Transfer-Encoding', 'binary');

        if ($streamAsAttachment) {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $file->getName() . '"');
        }

        $response->setCallback(function () use ($file) {
            $this->fileReader->read($file->getFilePartCollection());
        });

        return $response;
    }
}
