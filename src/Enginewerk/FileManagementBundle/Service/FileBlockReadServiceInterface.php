<?php
namespace Enginewerk\FileManagementBundle\Service;

use Enginewerk\FileManagementBundle\FileResponse\BinaryBlockCollection;
use Enginewerk\FileManagementBundle\Storage\FileNotFoundException;
use Enginewerk\FileManagementBundle\Storage\InvalidFileIdentifierException;

interface FileBlockReadServiceInterface
{
    /**
     * @param string $fileShortIdentifier
     *
     * @throws InvalidFileIdentifierException
     * @throws FileNotFoundException
     *
     * @return BinaryBlockCollection
     *
     */
    public function getFileBlockCollection($fileShortIdentifier);
}
