<?php
namespace Enginewerk\FSBundle\Service;

use Symfony\Component\HttpFoundation\File\File as SystemFile;

interface BinaryStorageInterface
{
    /**
     * @param SystemFile $file
     * @param string $key
     *
     * @return int
     */
    public function store(SystemFile $file, $key);

    /**
     * @param string $key
     *
     * @return SystemFile
     */
    public function get($key);

    /**
     * @param string $key
     *
     * @throws \Exception
     */
    public function delete($key);
}
