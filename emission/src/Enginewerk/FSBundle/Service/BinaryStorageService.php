<?php
namespace Enginewerk\FSBundle\Service;

use Enginewerk\FSBundle\Entity\BinaryBlock;
use Enginewerk\FSBundle\Repository\BinaryBlockRepositoryInterface;
use Enginewerk\FSBundle\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\File\File as SystemFile;

class BinaryStorageService implements BinaryStorageInterface
{
    /** @var BinaryBlockRepositoryInterface */
    private $binaryBlockRepository;

    /** @var StorageInterface */
    private $binaryBlockStorage;

    /**
     * @param BinaryBlockRepositoryInterface $binaryBlockRepository
     * @param StorageInterface $binaryBlockStorage
     */
    public function __construct(
        BinaryBlockRepositoryInterface $binaryBlockRepository,
        StorageInterface $binaryBlockStorage
    ) {
        $this->binaryBlockRepository = $binaryBlockRepository;
        $this->binaryBlockStorage = $binaryBlockStorage;
    }

    /**
     * @inheritdoc
     */
    public function store(SystemFile $file, $key)
    {
        $checksum = md5_file($file->getPathname()); // Path in local system
        $size = $file->getSize();

        $binaryBlock = new BinaryBlock();
        $binaryBlock->setUrn($key);
        $binaryBlock->setChecksum($checksum);
        $binaryBlock->setSize($size);

        $this->binaryBlockRepository->persist($binaryBlock);
        $this->binaryBlockStorage->put($key, $file);

        return $size;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return $this->binaryBlockStorage->get($this->getBlockByResourceName($key)->getUrn());
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $binaryBlock = $this->getBlockByResourceName($key);
        $urn = $binaryBlock->getUrn();
        $this->binaryBlockRepository->remove($binaryBlock);
        $this->binaryBlockStorage->delete($urn);
    }

    /**
     * @param string $name
     *
     * @return BinaryBlock
     */
    private function getBlockByResourceName($name)
    {
        return $this->binaryBlockRepository->findOneByUniformResourceName($name);
    }
}
