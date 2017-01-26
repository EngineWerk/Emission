<?php
namespace Enginewerk\EmissionBundle\FileResponse;

use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;

class FileBinaryBlockCollection implements FileReadInterface
{
    /** @var  File[] */
    private $fileBinaryBlockCollection;

    /**
     * @param File[] $fileBinaryBlockCollection
     */
    public function __construct(array $fileBinaryBlockCollection)
    {
        $this->fileBinaryBlockCollection = $fileBinaryBlockCollection;
    }

    public function read()
    {
        $this->checkIfFileIsReadable();

        foreach ($this->getFileBinaryBlockCollection() as $chunk) {
            readfile($chunk->getPathname());
        }
    }

    protected function checkIfFileIsReadable()
    {
        foreach ($this->getFileBinaryBlockCollection() as $chunk) {
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
    protected function getFileBinaryBlockCollection()
    {
        return $this->fileBinaryBlockCollection;
    }
}
