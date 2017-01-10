<?php
namespace Enginewerk\FSBundle\Storage;

use Enginewerk\FSBundle\Entity\BinaryBlock;
use Exception;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\File\File;

class StorageService
{
    /** @var RegistryInterface */
    protected $doctrine;

    protected $storage;

    public function __construct(RegistryInterface $doctrine, $storage)
    {
        $this->doctrine = $doctrine;
        $this->storage = $storage;
    }

    /**
     *
     * @param  string $key
     * @param  \Symfony\Component\HttpFoundation\File\File $uploadedFile
     *
     * @return int
     */
    public function put($key, File $uploadedFile)
    {
        $checksum = md5_file($uploadedFile->getPathname());
        $size = $uploadedFile->getSize();

        $block = new BinaryBlock();
        $block->setUrn($key);
        $block->setChecksum($checksum);
        $block->setSize($size);

        $this
                ->getDoctrine()
                ->getManager()
                ->persist($block);

        $this
                ->storage
                ->put($key, $uploadedFile);

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
        $block = $this->getBlock($key);
        $file = $this->storage->get($block->getUrn());

        return $file;
    }

    private function getBlock($key)
    {
        return $this
                ->getDoctrine()
                ->getRepository('EnginewerkFSBundle:BinaryBlock')
                ->findOneByUrn($key);
    }

    public function delete($key)
    {
        $em = $this
                ->getDoctrine()
                ->getManager();

        $block = $this->getBlock($key);

        $em->getConnection()->beginTransaction();
        $em->remove($block);

        try {
            $this->storage->delete($block->getUrn());
            $em->flush();

            $em->getConnection()->commit();
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $em->close();
            throw $e;
        }
    }

    /**
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private function getDoctrine()
    {
        return $this->doctrine;
    }
}
