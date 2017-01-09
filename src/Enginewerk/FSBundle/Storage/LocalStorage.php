<?php
namespace Enginewerk\FSBundle\Storage;

use RuntimeException;

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
     * Stores File under given key
     *
     * @param  type                                        $key
     * @param  \Symfony\Component\HttpFoundation\File\File $uploadedFile
     *
     * @return int
     */
    public function put($key, $uploadedFile)
    {
        $size = $uploadedFile->getSize();
        $path = $this->getStorageRootDirectory() . DIRECTORY_SEPARATOR . $this->getDeepDirFromFileName($key);
        $uploadedFile->move($path, $key);

        return $size;
    }

    /**
     * Returns File object for given key
     *
     * @param string $key
     *
     * @return \Enginewerk\FSBundle\Storage\File
     */
    public function get($key)
    {
        $pathname = $this->pathname($key);

        $file = new File($pathname);

        return $file;
    }

    /**
     * Deletes file with given key
     *
     * @param string $key
     *
     * @throws \RuntimeException
     *
     * @return bool|void
     */
    public function delete($key)
    {
        $pathname = $this->pathname($key);

        if (file_exists($pathname) === false) {
            throw new RuntimeException(sprintf('File with key "%s" not found under path "%s".', $key, $pathname));
        }

        if (unlink($pathname) === false) {
            throw new RuntimeException(sprintf('Can`t delete file with key "%s" from path "%s"', $key, $pathname));
        }
    }

    /**
     * Return storage directory root path
     *
     * @return string
     */
    private function getStorageRootDirectory()
    {
        return $this->storageRootDirectory;
    }

    /**
     * Returns nested path based on given name
     *
     * @param  string $name
     *
     * @return string
     */
    private function getDeepDirFromFileName($name)
    {
        return $name[0] . $name[1] . DIRECTORY_SEPARATOR .
                $name[2] . $name[3] . DIRECTORY_SEPARATOR .
                $name[4] . $name[5] . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns pathname for given key
     *
     * @param  string $key
     *
     * @return string
     */
    private function pathname($key)
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->getStorageRootDirectory(),
            $this->getDeepDirFromFileName($key),
            $key, ]
                );
    }
}
