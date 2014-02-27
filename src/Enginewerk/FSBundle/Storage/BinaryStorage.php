<?php

namespace Enginewerk\FSBundle\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Enginewerk\FSBundle\Entity\BinaryBlock;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Enginewerk\FSBundle\Storage\File;

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
        
        $block = new BinaryBlock();
        $block->setChecksum($checksum);
        $block->setSize($size);

        $this
                ->getDoctrine()
                ->getManager()
                ->persist($block);

        $path = $this->getStorageRootDirectory() . DIRECTORY_SEPARATOR . $this->getDeepDirFromFileName($checksum);
        $uploadedFile->move($path, $checksum);
        
        return $block;
    }
    
    /**
     * 
     * @param string $key
     * @return Enginewerk\FSBundle\Storage\File
     */
    public function get($key)
    {
        $block = $this
                ->getDoctrine()
                ->getRepository('EnginewerkFSBundle:BinaryBlock')
                ->findOneByChecksum($key);
        
        $file = new File();
        $file->setChecksum($block->getChecksum());
        
        $pathname = implode(DIRECTORY_SEPARATOR, array(
            $this->getStorageRootDirectory(), 
            $this->getDeepDirFromFileName($block->getChecksum()), 
            $block->getChecksum())
                );
        
        $file->setPathname($pathname);
        $file->setSize($block->getSize());

        return $file;
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
        return $this->storageRootDirectory;
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
