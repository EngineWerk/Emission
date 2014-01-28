<?php

namespace Enginewerk\EmissionBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Enginewerk\EmissionBundle\Entity\FileBlock;

/**
 * FileBlockSubscriber
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class FileBlockSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'preRemove',
            'prePersist'
        );
    }
    
    public function preRemove(LifecycleEventArgs $args)
    {
        $FileBlock = $args->getEntity();
        
        if ($FileBlock instanceof FileBlock) {
            $query = $args->getEntityManager()
                    ->createQuery('SELECT COUNT(f.id) as totalNumber FROM EnginewerkEmissionBundle:FileBlock f WHERE f.fileHash = :fileHash')
                    ->setParameter('fileHash', $FileBlock->getFileHash());
            
            $totalNumber = $query->getSingleScalarResult();

            if(null === $totalNumber || 1 == $totalNumber) {
                
                $em = $args->getEntityManager();
                $Block = $em->getRepository('EnginewerkEmissionBundle:BinaryBlock')->findOneByChecksum($FileBlock->getFileHash());
                $em->remove($Block);
                $em->flush();
            }
        }
    }
    
    public function prePersist(LifecycleEventArgs $args)
    {
        $fileBlock = $args->getEntity();
        
        if ($fileBlock instanceof FileBlock) {
            $createdAt = new \DateTime('now');
            $fileBlock->setUpdatedAt($createdAt);
            $fileBlock->setCreatedAt($createdAt);
        }
    }
}
