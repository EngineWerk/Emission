<?php
namespace Enginewerk\EmissionBundle\FileResponse;

use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;

class ChunkedFile implements FileReadInterface
{
    /** @var  File[] */
    protected $fileChunks;

    /**
     * @param File[] $fileChunks
     */
    public function __construct(array $fileChunks)
    {
        $this->fileChunks = $fileChunks;
    }

    public function read()
    {
        $this->checkIfFileIsReadable();

        foreach ($this->getChunks() as $chunk) {
            readfile($chunk->getPathname());
        }
    }

    protected function checkIfFileIsReadable()
    {
        foreach ($this->getChunks() as $chunk) {
            if (!$chunk->isFile() || !$chunk->isReadable()) {
                throw new RuntimeException(
                    sprintf(
                        'File part is not readable from path "%s"',
                        $chunk->getPathname()
                    )
                );
            }
        }
    }

    /**
     * @return File[]
     */
    protected function getChunks()
    {
        return $this->fileChunks;
    }
}
