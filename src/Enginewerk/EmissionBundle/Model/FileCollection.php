<?php
namespace Enginewerk\EmissionBundle\Model;

class FileCollection
{
    /** @var  File[] */
    private $collection;

    /**
     * @param File $file
     */
    public function add(File $file)
    {
        $this->collection[] = $file;
    }

    /**
     * @return File[]
     */
    public function getAll()
    {
        return $this->collection;
    }
}
