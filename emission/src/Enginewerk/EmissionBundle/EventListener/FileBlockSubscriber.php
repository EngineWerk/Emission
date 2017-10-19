<?php
namespace Enginewerk\EmissionBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Enginewerk\ApplicationBundle\DateTime\DateTimeReadInterface;
use Enginewerk\EmissionBundle\Entity\FileBlock;

class FileBlockSubscriber implements EventSubscriber
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
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $fileBlock = $args->getEntity();

        if ($fileBlock instanceof FileBlock) {
            $currentTime = $this->dateTimeReader->getCurrentDateTime();
            $fileBlock->setUpdatedAt($currentTime);
            $fileBlock->setCreatedAt($currentTime);
        }
    }
}
