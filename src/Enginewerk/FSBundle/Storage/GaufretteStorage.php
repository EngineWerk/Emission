<?php
namespace Enginewerk\FSBundle\Storage;

use Gaufrette\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class GaufretteStorage implements StorageInterface
{
    /** @var Filesystem  */
    protected $filesystem;

    /**
     * @var string
     */
    protected $filesystemName;

    /**
     * @param Filesystem $filesystem
     * @param string     $filesystemName
     */
    public function __construct(Filesystem $filesystem, $filesystemName)
    {
        $this->filesystem = $filesystem;
        $this->filesystemName = $filesystemName;
    }

    /**
     * @inheritdoc
     */
    public function put($key, File $uploadedFile)
    {
        $pathname = $this->getPathName($key);
        $size = $this->filesystem
            ->write(
                $pathname,
                file_get_contents($uploadedFile->getPathname())
            );

        return $size;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $pathname = $this->getPathName($key);
        $file = new File('gaufrette://' . $this->filesystemName . DIRECTORY_SEPARATOR . $pathname);

        return $file;
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $pathname = $this->getPathName($key);
        $this->filesystem
            ->delete($pathname);
    }

    /**
     * @param  string $key
     *
     * @return string
     */
    private function getPathName($key)
    {
        return $this->getDeepDirFromFileName($key) . DIRECTORY_SEPARATOR . $key;
    }

    /**
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
}
