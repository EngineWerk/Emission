<?php
namespace Enginewerk\FSBundle\Storage;

use Enginewerk\FSBundle\Storage\Exception\FileNotFoundException;
use Enginewerk\FSBundle\Storage\Exception\SystemStorageException;
use Symfony\Component\HttpFoundation\File\File;

class LocalStorage implements StorageInterface
{
    /** @var  string */
    private $storageRootDirectory;

    /**
     * @param string $storageRootDirectory
     */
    public function __construct($storageRootDirectory)
    {
        $this->storageRootDirectory = $storageRootDirectory;
    }

    /**
     * @inheritdoc
     */
    public function put($key, File $uploadedFile)
    {
        $size = $uploadedFile->getSize();
        $path = $this->getStorageRootDirectory() . DIRECTORY_SEPARATOR . $this->getDeepDirFromFileName($key);

        $uploadedFile->move($path, $key);

        return $size;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $pathname = $this->pathname($key);

        return new File($pathname);
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $pathname = $this->pathname($key);

        if (file_exists($pathname) === false) {
            throw new FileNotFoundException(sprintf('File with key "%s" not found under path "%s".', $key, $pathname));
        }

        if (unlink($pathname) === false) {
            throw new SystemStorageException(
                sprintf(
                    'Can`t delete file with key "%s" from path "%s" using "unlink" method.',
                    $key,
                    $pathname
                )
            );
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
