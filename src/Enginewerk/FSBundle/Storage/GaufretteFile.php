<?php
namespace Enginewerk\FSBundle\Storage;

/**
 * Description of GaufretteFile
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class GaufretteFile extends File
{
    public function __construct($path)
    {
        parent::__construct($path, false);
    }

    public function isDir()
    {
        return false;
    }

    public function isExecutable()
    {
        return false;
    }

    public function isFile()
    {
        return true;
    }

    public function isLink()
    {
        return false;
    }

    public function isReadable()
    {
        return true;
    }

    public function isWritable()
    {
        return true;
    }
}
