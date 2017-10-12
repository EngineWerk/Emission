<?php
namespace Enginewerk\EmissionBundle\Stream;

use Enginewerk\EmissionBundle\Storage\Model\FilePartCollection;
use Enginewerk\EmissionBundle\Storage\UnreadableFileException;

interface FileReaderInterface
{
    /**
     * @param FilePartCollection $filePartCollection
     *
     * @throws UnreadableFileException
     *
     * @return void
     */
    public function read(FilePartCollection $filePartCollection);
}
