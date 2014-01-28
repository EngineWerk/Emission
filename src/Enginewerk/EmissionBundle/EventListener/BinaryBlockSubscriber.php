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
        $block = $args->getEntity();
        
        if ($block instanceof BinaryBlock) {
            $this->removeBinaryBlock($block);
        }
    }
    
    private function removeBinaryBlock(BinaryBlock $block)
    {
        $filePath = $block->getPathname();

        if(file_exists($filePath)) {
            if(false === unlink($filePath)) {
                throw new \Exception(sprintf('Can`t unlink file "%s"', $filePath));
            }
        }
    }
    
}
