<?php

namespace Enginewerk\FSBundle\Storage;

use Symfony\Component\HttpFoundation\File\File;
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
     * 
     * @param type $key
     * @param \Symfony\Component\HttpFoundation\File\File $uploadedFile
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

        $path = $this->getStorageRootDirectory() . DIRECTORY_SEPARATOR . $this->getDeepDirFromFileName($key);
        $uploadedFile->move($path, $key);
        
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
        
        $pathname = implode(DIRECTORY_SEPARATOR, array(
            $this->getStorageRootDirectory(), 
            $this->getDeepDirFromFileName($block->getUrn()), 
            $block->getUrn())
                );
        
        $file = new File($pathname);

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
        $file = $this->get($key);
        
        if (file_exists($file->getPathname())) {
            unlink($file->getPathname());
        }
        
        $em->remove($block);
        $em->flush();
    }
    
    public function getStorageRootDirectory()
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
