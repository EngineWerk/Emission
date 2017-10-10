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
     * @param string $publicIdentifier
     *
     * @return File|null
     */
    public function findByPublicIdentifier($publicIdentifier);

    /**
     * @param string $fileName
     * @param string $fileChecksum
     * @param int $fileSize
     *
     * @return File|null
     */
    public function findOneByNameAndChecksumAndSize($fileName, $fileChecksum, $fileSize);

    /**
     * @param File $file
     *
     * @return void
     */
    public function remove(File $file);

    /**
     * @param File $file
     *
     * @return void
     */
    public function persist(File $file);
}
