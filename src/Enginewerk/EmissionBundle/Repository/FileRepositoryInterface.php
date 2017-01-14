<?php
namespace Enginewerk\EmissionBundle\Repository;

use Enginewerk\EmissionBundle\Entity\File;

interface FileRepositoryInterface
{
    /**
     * @return File[]
     */
    public function getFiles();

    /**
     * @param \DateTimeInterface|null $nowDate
     *
     * @return File[]
     */
    public function getExpiredFiles(\DateTimeInterface $nowDate = null);

    /**
     * @param \DateTimeInterface|null $createdAfter
     *
     * @return string[]
     */
    public function getFilesForJsonApi(\DateTimeInterface $createdAfter = null);

    /**
     * @param string $shortIdentifier
     *
     * @return File|null
     */
    public function findOneByShortIdentifier($shortIdentifier);

    /**
     * @param File $file
     *
     * @return void
     */
    public function update(File $file);

    /**
     * @param File $file
     *
     * @return void
     */
    public function remove(File $file);
}
