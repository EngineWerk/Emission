<?php

namespace Enginewerk\EmissionBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of FileRepository
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class FileRepository extends EntityRepository
{
    public function getFiles()
    {
        $query = $this->createQueryBuilder('f')
                ->orderBy('f.id', 'DESC')
                ->getQuery();

        return $query->getResult();
    }
    
    public function getExpiredFiles(\DateTime $nowDate = null)
    {
        if(null === $nowDate) {
            $nowDate = new \DateTime('now');
        }
        
        $query = $this->createQueryBuilder('f')
                ->where('f.expirationDate < ?1')
                ->setParameter(1, $nowDate->format('Y-m-d H:i:s'))
                ->getQuery();
        
        return $query->getResult();
    }
    
    public function getFilesForJsonApi($createdAfter = null)
    {
        $queryBuilder = $this->createQueryBuilder('f')
                ->select('f.fileId, f.name, f.checksum, f.size, f.type, f.expirationDate, f.isComplete')
                ->orderBy('f.id', 'DESC');
        
        if($createdAfter) {
            $queryBuilder
                    ->where('f.createdAt > ?1')
                    ->setParameter(1, $createdAfter->format('Y-m-d H:i:s'));
        }
        
        $query = $queryBuilder->getQuery();

        return $query->getArrayResult();
    }
}
