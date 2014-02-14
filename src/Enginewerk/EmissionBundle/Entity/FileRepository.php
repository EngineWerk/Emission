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
    
    public function getFilesForJsonApi()
    {
        $query = $this->createQueryBuilder('f')
                ->select('f.fileId, f.name, f.checksum, f.size, f.type, f.expirationDate, f.uploadedBy, f.isComplete')
                ->orderBy('f.id', 'DESC')
                ->getQuery();

        return $query->getArrayResult();
    }
}