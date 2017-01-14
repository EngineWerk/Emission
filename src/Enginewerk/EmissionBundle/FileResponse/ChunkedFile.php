<?php
namespace Enginewerk\EmissionBundle\FileResponse;

class ChunkedFile implements FileInterface
{
    /**
     * @var array
     */
    protected $fileChunks;

    /**
     * @inheritdoc
     */
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
            /* @var $chunk \Enginewerk\FSBundle\Storage\File */
            if (!$chunk->isFile() || !$chunk->isReadable()) {
                throw new \Exception(sprintf('File part is not readable from path "%s"', $chunk->getPathname()));
            }
        }
    }

    public function setChunks(array $chunks)
    {
        $this->fileChunks = $chunks;
    }

    public function getChunks()
    {
        return $this->fileChunks;
    }
}
