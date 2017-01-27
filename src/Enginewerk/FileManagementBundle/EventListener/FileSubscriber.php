<?php
namespace Enginewerk\FileManagementBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Enginewerk\FileManagementBundle\Entity\File;

class FileSubscriber implements EventSubscriber
{
    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $file = $args->getEntity();

        if ($file instanceof File) {
            $file->preChange();
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $file = $args->getEntity();

        if ($file instanceof File) {
            $file->preChange();
        }
    }
}
