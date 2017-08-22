<?php
namespace Enginewerk\FSBundle\Storage;

use Enginewerk\FSBundle\Storage\Exception\FileNotFoundException;
use Enginewerk\FSBundle\Storage\Exception\SystemStorageException;
use Symfony\Component\HttpFoundation\File\File;

interface StorageInterface
{
    /**
     * @param  string $key
     * @param  File $uploadedFile
     *
     * @return int File size
     */
    public function put($key, File $uploadedFile);

    /**
     * @param  string $key
     *
     * @return File
     */
    public function get($key);

    /**
     * @param string $key
     *
     * @throws FileNotFoundException
     * @throws SystemStorageException
     *
     * @return bool
     *
     */
    public function delete($key);
}
