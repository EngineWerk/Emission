<?php

namespace Enginewerk\FSBundle\Storage;

use Enginewerk\FSBundle\Entity\BinaryBlock;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Description of StorageService
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class StorageService
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    protected $storage;

    public function __construct(Registry $doctrine, $storage)
    {
        $this->doctrine = $doctrine;
        $this->storage = $storage;
    }

    /**
     *
     * @param  type                                        $key
     * @param  \Symfony\Component\HttpFoundation\File\File $uploadedFile
     * @return integer
     */
    public function put($key, $uploadedFile)
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
     * @return \Symfony\Component\HttpFoundation\File\File
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

        $this->storage->delete($block->getUrn());

        $em->remove($block);
        $em->flush();
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