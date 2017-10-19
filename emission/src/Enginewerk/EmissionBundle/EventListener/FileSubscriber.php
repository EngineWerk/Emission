<?php
namespace Enginewerk\EmissionBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Enginewerk\ApplicationBundle\DateTime\DateTimeReadInterface;
use Enginewerk\EmissionBundle\Entity\File as FileEntity;

class FileSubscriber implements EventSubscriber
{
    /** @var DateTimeReadInterface */
    private $dateTimeReader;

    /**
     * @param DateTimeReadInterface $dateTimeReader
     */
    public function __construct(DateTimeReadInterface $dateTimeReader)
    {
        $this->dateTimeReader = $dateTimeReader;
    }

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

        if ($file instanceof FileEntity) {
            $currentTime = $this->dateTimeReader->getCurrentDateTime();
            $file->setCreatedAt($currentTime);
            $file->setUpdatedAt($currentTime);
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $file = $args->getEntity();

        if ($file instanceof FileEntity) {
            $file->setUpdatedAt($this->dateTimeReader->getCurrentDateTime());
        }
    }
}
