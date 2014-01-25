<?php

namespace Enginewerk\EmissionBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Enginewerk\EmissionBundle\Entity\BinaryBlock;

/**
 * BinaryBlockSubscriber
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class BinaryBlockSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'postRemove',
        );
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->removeUpload($args);
    }
    
    public function removeUpload(LifecycleEventArgs $args)
    {
        $Block = $args->getEntity();
        
        if ($Block instanceof BinaryBlock) {
            $filePath = $Block->getPathname();

            if(file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
    
}
