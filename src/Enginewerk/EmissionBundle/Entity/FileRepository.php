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
}
