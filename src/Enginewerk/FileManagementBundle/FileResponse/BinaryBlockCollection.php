<?php
namespace Enginewerk\FileManagementBundle\FileResponse;

use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;

class BinaryBlockCollection implements FileReadInterface
{
    /** @var  File[] */
    private $binaryBlockCollection;

    /**
     * @param File[] $fileBinaryBlockCollection
     */
    public function __construct(array $fileBinaryBlockCollection)
    {
        $this->binaryBlockCollection = $fileBinaryBlockCollection;
    }

    public function read()
    {
        $this->checkIfFileIsReadable();

        foreach ($this->getBinaryBlockCollection() as $chunk) {
            readfile($chunk->getPathname());
        }
    }

    protected function checkIfFileIsReadable()
    {
        foreach ($this->getBinaryBlockCollection() as $chunk) {
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
    protected function getBinaryBlockCollection()
    {
        return $this->binaryBlockCollection;
    }
}
