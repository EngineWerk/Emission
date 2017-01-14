<?php
namespace Enginewerk\EmissionBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Enginewerk\EmissionBundle\Entity\FileBlock;

class FileBlockSubscriber implements EventSubscriber
{
    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
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
