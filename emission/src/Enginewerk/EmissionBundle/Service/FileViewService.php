<?php
namespace Enginewerk\EmissionBundle\Service;

use Enginewerk\EmissionBundle\Entity\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FileViewService implements FileViewServiceInterface
{
    /** @var UrlGeneratorInterface */
    protected $routeGenerator;

    /**
     * @param UrlGeneratorInterface $routeGenerator
     */
    public function __construct(UrlGeneratorInterface $routeGenerator)
    {
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * @inheritdoc
     */
    public function createResponseForIncompleteFile(File $file)
    {
        return [
            'id' => $file->getId(),
            'file_id' => $file->getFileId(),
            'name' => $file->getName(),
            'type' => $file->getType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function createResponseForCompleteFile(File $file)
    {
        return [
            'id' => $file->getId(),
            'file_id' => $file->getFileId(),
            'name' => $file->getName(),
            'type' => $file->getType(),
            'size' => $file->getSize(),
            'expiration_date' => $file->getExpirationDate()->format('Y-m-d H:i:s'),
            'updated_at' => $file->getUpdatedAt()->format('Y-m-d H:i:s'),
            'created_at' => $file->getCreatedAt()->format('Y-m-d H:i:s'),
            'uploaded_by' => $file->getUser()->getUsername(),
            'show_url' => $this->routeGenerator->generate(
                'show_file',
                ['file' => $file->getFileId()],
                true
            ),
            'download_url' => $this->routeGenerator->generate(
                'download_file',
                ['fileShortIdentifier' => $file->getFileId()],
                true
            ),
            'open_url' => $this->routeGenerator->generate(
                'open_file',
                ['fileShortIdentifier' => $file->getFileId()],
                true
            ),
            'delete_url' => $this->routeGenerator->generate(
                'delete_file',
                ['file' => $file->getFileId()],
                true
            ),
        ];
    }
}
