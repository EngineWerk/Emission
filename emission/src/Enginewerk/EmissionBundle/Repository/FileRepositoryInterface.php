<?php
namespace Enginewerk\EmissionBundle\Repository;

use Enginewerk\ApplicationBundle\Repository\NoResultException;
use Enginewerk\EmissionBundle\Entity\File as FileEntity;

interface FileRepositoryInterface
{
    /**
     * @return FileEntity[]
     */
    public function getFiles();

    /**
     * @param \DateTimeInterface|null $nowDate
     *
     * @return FileEntity[]
     */
    public function getExpiredFiles(\DateTimeInterface $nowDate = null);

    /**
     * @param \DateTimeInterface|null $createdAfter
     *
     * @return string[]
     */
    public function findAllAsArray(\DateTimeInterface $createdAfter = null);

    /**
     * @param string $publicIdentifier
     *
     * @return FileEntity|null
     */
    public function findByPublicIdentifier($publicIdentifier);

    /**
     * @param string $publicIdentifier
     *
     * @throws NoResultException
     *
     * @return FileEntity
     */
    public function getByPublicIdentifier($publicIdentifier);

    /**
     * @param string $fileName
     * @param string $fileChecksum
     * @param int $fileSize
     *
     * @return FileEntity|null
     */
    public function findOneByNameAndChecksumAndSize($fileName, $fileChecksum, $fileSize);

    /**
     * @param FileEntity $file
     *
     * @return void
     */
    public function remove(FileEntity $file);

    /**
     * @param FileEntity $file
     *
     * @return void
     */
    public function persist(FileEntity $file);
}
