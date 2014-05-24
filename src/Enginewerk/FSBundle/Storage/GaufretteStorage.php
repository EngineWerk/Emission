<?php

namespace Enginewerk\FSBundle\Storage;

use Enginewerk\FSBundle\Storage\GaufretteFile as File;

/**
 * Description of GaufretteStorage
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class GaufretteStorage implements StorageInterface
{
    /**
     *
     * @var \Gaufrette\Filesystem
     */
    protected $filesystem;

    protected $filesystemName;

    public function __construct(\Gaufrette\Filesystem $filesystem, $filesystemName)
    {
        $this->filesystem = $filesystem;
        $this->filesystemName = $filesystemName;
    }

    /**
     *
     * @param  type                                        $key
     * @param  \Symfony\Component\HttpFoundation\File\File $uploadedFile
     * @return integer
     */
    public function put($key, $uploadedFile)
    {
        $pathname = $this->getPathName($key);
        $size = $this
                ->filesystem
                ->write($pathname, file_get_contents($uploadedFile->getPathname()));

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
        $pathname = $this->getPathName($key);
        $file = new File('gaufrette://' . $this->filesystemName . DIRECTORY_SEPARATOR . $pathname);

        return $file;
    }

    public function delete($key)
    {
        $pathname = $this->getPathName($key);
        $this
                ->filesystem
                ->delete($pathname);
    }

    private function getPathName($key)
    {
        return $this->getDeepDirFromFileName($key) . DIRECTORY_SEPARATOR . $key;
    }

    private function getDeepDirFromFileName($name)
    {
        return $name[0] . $name[1] . DIRECTORY_SEPARATOR .
                $name[2] . $name[3] . DIRECTORY_SEPARATOR .
                $name[4] . $name[5] . DIRECTORY_SEPARATOR;
    }
}
