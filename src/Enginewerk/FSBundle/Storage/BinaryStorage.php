<?php

namespace Enginewerk\FSBundle\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Enginewerk\FSBundle\Entity\BinaryBlock;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Description of BinaryStorage
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class BinaryStorage
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;
    
    protected $storageRootDirectory;


    public function __construct(Registry $doctrine, $storageRootDirectory)
    {
        $this->doctrine = $doctrine;
        $this->storageRootDirectory = $storageRootDirectory;
    }
    
    /**
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @return \Enginewerk\FSBundle\Entity\BinaryBlock
     */
    public function put(UploadedFile $uploadedFile)
    {
        $checksum = md5_file($uploadedFile->getPathname());

        $size = $uploadedFile->getSize();
        $path = $this->getStorageRootDirectory() . DIRECTORY_SEPARATOR . $this->getDeepDirFromFileName($checksum);

        $block = new BinaryBlock();
        $block->setChecksum($checksum);
        $block->setSize($size);
        $block->setPathname($path . DIRECTORY_SEPARATOR . $checksum);

        $this
                ->getDoctrine()
                ->getManager()
                ->persist($block);

        $uploadedFile->move($path, $checksum);
        
        return $block;
    }
    
    public function get($key)
    {
        $block = $this
                ->getDoctrine()
                ->getRepository('EnginewerkFSBundle:BinaryBlock')
                ->findOneByChecksum($key);
        
        return $block;
    }
    
    public function delete($key)
    {
        $em = $this
                ->getDoctrine()
                ->getManager();
        
        $block = $this
                ->getDoctrine()
                ->getRepository('EnginewerkFSBundle:BinaryBlock')
                ->findOneByChecksum($key);
        
        $em->remove($block);
        $em->flush();
    }
    
    private function getStorageRootDirectory()
    {
        return $this->storageRootDirectory . DIRECTORY_SEPARATOR . 'local_fs' . DIRECTORY_SEPARATOR . 'block' ;
    }

    private function getDeepDirFromFileName($name)
    {
        return $name[0] . $name[1] . DIRECTORY_SEPARATOR .
                $name[2] . $name[3] . DIRECTORY_SEPARATOR .
                $name[4] . $name[5] . DIRECTORY_SEPARATOR;
    }
    
    /**
     * 
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }
}
