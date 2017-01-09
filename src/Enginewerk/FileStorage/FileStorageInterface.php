<?php
namespace Enginewerk\FileStorage;

use Enginewerk\FileStorage\File\FileRequestInterface;
use Enginewerk\FileStorage\File\FileResponseInterface;

interface FileStorageInterface
{
    /**
     * @param FileRequestInterface $fileRequest
     *
     * @return bool
     */
    public function has(FileRequestInterface $fileRequest);

    /**
     * @param FileRequestInterface $fileRequest
     *
     * @return bool
     */
    public function put(FileRequestInterface $fileRequest);

    /**
     * @param FileRequestInterface $fileRequest
     *
     * @return FileResponseInterface
     */
    public function get(FileRequestInterface $fileRequest);

    /**
     * @param FileRequestInterface $fileRequest
     *
     * @return bool
     */
    public function delete(FileRequestInterface $fileRequest);
}
