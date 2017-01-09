<?php
namespace Enginewerk\EmissionBundle\FileResponse;

/**
 * Description of ChunkedFile.
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class ChunkedFile implements FileInterface
{
    protected $fileChunks;

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

    public function setChunks($chunks)
    {
        $this->fileChunks = $chunks;
    }

    public function getChunks()
    {
        return $this->fileChunks;
    }
}
