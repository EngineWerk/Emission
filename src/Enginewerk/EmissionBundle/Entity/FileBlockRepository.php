<?php

namespace Enginewerk\EmissionBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of FileBlockRepository.
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class FileBlockRepository extends EntityRepository
{
    public function getUsedBlocksNumber($fileHash)
    {
        $query = $this->getEntityManager()
                ->createQuery('SELECT COUNT(f.id) as totalNumber FROM EnginewerkEmissionBundle:FileBlock f WHERE f.fileHash = :fileHash')
                ->setParameter('fileHash', $fileHash);

        return $query->getSingleScalarResult();
    }

    public function getTotalSize($fileId)
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT SUM(f.size) as totalSize FROM EnginewerkEmissionBundle:FileBlock f WHERE f.fileId = :fileId')
            ->setParameter('fileId', $fileId);

        return $query->getSingleScalarResult();
    }
}
