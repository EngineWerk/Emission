<?php
namespace Enginewerk\EmissionBundle\Storage;

use Enginewerk\EmissionBundle\Storage\Model\FilePart;
use Enginewerk\EmissionBundle\Storage\Model\FilePartCollection;
use Enginewerk\EmissionBundle\Storage\Model\SystemFileCollection;
use Enginewerk\EmissionBundle\Stream\FileReaderInterface;
use Enginewerk\FSBundle\Service\BinaryStorageInterface;
use Symfony\Component\HttpFoundation\File\File;

final class FileReader implements FileReaderInterface
{
    /** @var BinaryStorageInterface */
    private $binaryStorage;

    /**
     * @param BinaryStorageInterface $binaryStorage
     */
    public function __construct(BinaryStorageInterface $binaryStorage)
    {
        $this->binaryStorage = $binaryStorage;
    }

    /**
     * @inheritdoc
     */
    public function read(FilePartCollection $filePartCollection)
    {
        $systemFileCollection = $this->filePartToSystemFile($filePartCollection);

        $this->checkIfFileIsReadable($systemFileCollection);

        /** @var File $systemFile */
        foreach ($systemFileCollection as $systemFile) {
            readfile($systemFile->getPathname());
        }
    }

    /**
     * @param SystemFileCollection $systemFileCollection
     *
     * @throws UnreadableFileException
     */
    private function checkIfFileIsReadable(SystemFileCollection $systemFileCollection)
    {
        /** @var File $systemFile */
        foreach ($systemFileCollection as $systemFile) {
            if (!$systemFile->isFile() || !$systemFile->isReadable()) {
                throw new UnreadableFileException(
                    sprintf(
                        'File part is not readable from path "%s"',
                        $systemFile->getPathname()
                    )
                );
            }
        }
    }

    /**
     * @param FilePartCollection $filePartCollection
     *
     * @return SystemFileCollection
     */
    private function filePartToSystemFile(FilePartCollection $filePartCollection)
    {
        $systemFileCollection = new SystemFileCollection();

        /** @var FilePart $filePart */
        foreach ($filePartCollection as $filePart) {
            $systemFileCollection->add($this->binaryStorage->get($filePart->getIdentifier()));
        }

        return $systemFileCollection;
    }
}
