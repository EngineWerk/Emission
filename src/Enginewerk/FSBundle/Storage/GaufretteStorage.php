<?php

namespace Enginewerk\FSBundle\Storage;

use Enginewerk\FSBundle\Storage\GaufretteFile as File;
use Gaufrette\Filesystem;

/**
 * Description of GaufretteStorage.
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
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
     * @param string $key
     * @param File   $uploadedFile
     *
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
     * @param string $key
     *
     * @return File
     */
    public function get($key)
    {
        $pathname = $this->getPathName($key);
        $file = new File('gaufrette://'.$this->filesystemName.DIRECTORY_SEPARATOR.$pathname);

        return $file;
    }

    /**
     * @param  string    $key
     * @return bool|void
     */
    public function delete($key)
    {
        $pathname = $this->getPathName($key);
        $this
                ->filesystem
                ->delete($pathname);
    }

    /**
     * @param  string $key
     * @return string
     */
    private function getPathName($key)
    {
        return $this->getDeepDirFromFileName($key).DIRECTORY_SEPARATOR.$key;
    }

    /**
     * @param  string $name
     * @return string
     */
    private function getDeepDirFromFileName($name)
    {
        return $name[0].$name[1].DIRECTORY_SEPARATOR.
                $name[2].$name[3].DIRECTORY_SEPARATOR.
                $name[4].$name[5].DIRECTORY_SEPARATOR;
    }
}
