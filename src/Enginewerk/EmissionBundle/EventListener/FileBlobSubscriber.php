<?php

namespace Enginewerk\EmissionBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Enginewerk\EmissionBundle\Entity\FileBlob;

/**
 * FileBlobSubscriber
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class FileBlobSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'preRemove'
        );
    }
    
    public function preRemove(LifecycleEventArgs $args)
    {
        $FileBlob = $args->getEntity();
        
        if ($FileBlob instanceof FileBlob) {
            $query = $args->getEntityManager()
                    ->createQuery('SELECT COUNT(f.id) as totalNumber FROM EnginewerkEmissionBundle:FileBlob f WHERE f.fileHash = :fileHash')
                    ->setParameter('fileHash', $FileBlob->getFileHash());
            
            $totalNumber = $query->getSingleScalarResult();

            if(null === $totalNumber || 1 == $totalNumber) {
                
                $em = $args->getEntityManager();
                $Block = $em->getRepository('EnginewerkEmissionBundle:BinaryBlock')->findOneByChecksum($FileBlob->getFileHash());
                $em->remove($Block);
                $em->flush();
            }
        }
    }
}
