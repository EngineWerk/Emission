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


    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    /**
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @return \Enginewerk\FSBundle\Entity\BinaryBlock
     */
    public function put(UploadedFile $uploadedFile)
    {
        $checksum = md5_file($uploadedFile->getPathname());

        $size = $uploadedFile->getSize();
        $path = $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->getDeepDirFromFileName($checksum);

        $block = new BinaryBlock();
        $block->setChecksum($checksum);
        $block->setSize($size);
        $block->setPathname($path . DIRECTORY_SEPARATOR . $checksum);

        $this->doctrine->getManager()->persist($block);

        $uploadedFile->move($path, $checksum);
        
        return $block;
    }
    
    public function get($key)
    {
        $block = $this->doctrine
                ->getRepository('EnginewerkFSBundle:BinaryBlock')->findOneByChecksum($key);
        
        return $block;
    }
    
    public function delete($key)
    {
        $em = $this->doctrine->getManager();
        $block = $this->doctrine->getRepository('EnginewerkFSBundle:BinaryBlock')->findOneByChecksum($key);
        $em->remove($block);
        $em->flush();
    }
    
    private function getUploadRootDir()
    {
        $pathComponents = explode(DIRECTORY_SEPARATOR, __DIR__);
        $pathComponents = array_slice($pathComponents, 0, (count($pathComponents) - 4));
        $path = implode(DIRECTORY_SEPARATOR, $pathComponents);

        return $path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'local_fs' . DIRECTORY_SEPARATOR . 'block' ;
    }

    private function getDeepDirFromFileName($name)
    {
        return $name[0] . $name[1] . DIRECTORY_SEPARATOR .
                $name[2] . $name[3] . DIRECTORY_SEPARATOR .
                $name[4] . $name[5] . DIRECTORY_SEPARATOR;
    }
      
}
