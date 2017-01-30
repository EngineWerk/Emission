<?php
namespace Enginewerk\FileManagementBundle\Model;

class FileCollection
{
    /** @var  File[] */
    private $collection;

    /**
     * @param File[] $collection
     */
    public function __construct(array $collection = [])
    {
        foreach ($collection as $item) {
            $this->add($item);
        }
    }

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
