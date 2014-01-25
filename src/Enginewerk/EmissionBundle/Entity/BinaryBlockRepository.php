<?php

namespace Enginewerk\EmissionBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * BinaryBlockRepository
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class BinaryBlockRepository extends EntityRepository
{
    /**
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @return \Enginewerk\EmissionBundle\Entity\Block
     */
    public function storeUploadedFile(UploadedFile $uploadedFile)
    {
        $checksum = md5_file($uploadedFile->getPathname());

        return $this->storeFile($uploadedFile, $checksum);
    }

    public function removeBlockByChecksum($checksum)
    {
        $block = $this->findOneByChecksum($checksum);
        if ($block) {
            $this->getEntityManager()->remove($block);
        }
    }

    private function storeFile($file, $checksum)
    {
        $size = $file->getSize();
        $path = $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->getDeepDirFromFileName($checksum);

        $block = new BinaryBlock();
        $block->setChecksum($checksum);
        $block->setSize($size);
        $block->setPathname($path . DIRECTORY_SEPARATOR . $checksum);

        $this->getEntityManager()->persist($block);

        $file->move($path, $checksum);

        return $block;
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
