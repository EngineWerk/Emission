<?php

namespace Enginewerk\FSBundle\Storage;

use \RuntimeException;

/**
 * Description of LocalStorage
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class LocalStorage implements StorageInterface
{
    public function __construct($storageRootDirectory)
    {
        $this->storageRootDirectory = $storageRootDirectory;
    }

    /**
     *
     * @param  type                                        $key
     * @param  \Symfony\Component\HttpFoundation\File\File $uploadedFile
     * @return integer
     */
    public function put($key, $uploadedFile)
    {
        $size = $uploadedFile->getSize();
        $path = $this->getStorageRootDirectory() . DIRECTORY_SEPARATOR . $this->getDeepDirFromFileName($key);
        $uploadedFile->move($path, $key);

        return $size;
    }

    /**
     *
     * @param string $key
     *
     * @return \Enginewerk\FSBundle\Storage\File
     */
    public function get($key)
    {
        $pathname = implode(DIRECTORY_SEPARATOR, array(
            $this->getStorageRootDirectory(),
            $this->getDeepDirFromFileName($key),
            $key)
                );

        $file = new File($pathname);

        return $file;
    }

    public function delete($key)
    {
        if (file_exists($key) === false) {
            throw new RuntimeException(sprintf('File with key "%s" not found.', $key));
        }
        
        if (unlink($key) === false) {
            throw new RuntimeException(sprintf('Can`t delete file with key "%s"', $key));
        }
    }

    private function getStorageRootDirectory()
    {
        return $this->storageRootDirectory;
    }

    private function getDeepDirFromFileName($name)
    {
        return $name[0] . $name[1] . DIRECTORY_SEPARATOR .
                $name[2] . $name[3] . DIRECTORY_SEPARATOR .
                $name[4] . $name[5] . DIRECTORY_SEPARATOR;
    }
}
