<?php
namespace Enginewerk\FSBundle\Service;

use Enginewerk\FSBundle\Storage\Exception\FileNotFoundException;
use Enginewerk\FSBundle\Storage\Exception\SystemStorageException;
use Symfony\Component\HttpFoundation\File\File;

interface BinaryStorageServiceInterface
{
    /**
     * @param File $file
     * @param string $key
     *
     * @return int
     */
    public function store(File $file, $key);

    /**
     * @param string $key
     *
     * @return File
     */
    public function get($key);

    /**
     * @param string $key
     *
     * @throws FileNotFoundException
     * @throws SystemStorageException
     */
    public function delete($key);
}
