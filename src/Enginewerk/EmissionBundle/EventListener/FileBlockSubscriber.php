<?php

namespace Enginewerk\EmissionBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Enginewerk\EmissionBundle\Entity\FileBlock;

/**
 * FileBlockSubscriber.
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class FileBlockSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            'prePersist'
        );
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
