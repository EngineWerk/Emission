<?php
namespace Enginewerk\EmissionBundle\Service;

use Enginewerk\EmissionBundle\Presentation\Model\FileView;
use Enginewerk\EmissionBundle\Presentation\Model\FileViewCollection;
use Enginewerk\EmissionBundle\Storage\FileNotFoundException;

interface FileViewFinderInterface
{
    /**
     * @return FileViewCollection
     */
    public function findAll();

    /**
     * @param \DateTimeInterface|null $nowDate
     *
     * @return FileViewCollection
     */
    public function findExpiredFiles(\DateTimeInterface $nowDate = null);

    /**
     * @param string $identifier
     *
     * @return FileView|null
     */
    public function findByPublicIdentifier($identifier);

    /**
     * @param string $identifier
     *
     * @throws FileNotFoundException
     *
     * @return FileView
     */
    public function getByPublicIdentifier($identifier);
}
