<?php
namespace Enginewerk\FileManagementBundle\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException as DoctrineOptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Enginewerk\ApplicationBundle\Repository\InvalidEntityException;
use Enginewerk\ApplicationBundle\Repository\OptimisticLockException;
use Enginewerk\FileManagementBundle\Entity\File;
use Enginewerk\FileManagementBundle\Repository\FileRepositoryInterface;

class FileRepository extends EntityRepository implements FileRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function getFiles()
    {
        $query = $this
            ->createQueryBuilder('f')
            ->orderBy('f.id', 'DESC')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @inheritdoc
     */
    public function getExpiredFiles(\DateTimeInterface $nowDate = null)
    {
        if (null === $nowDate) {
            $nowDate = new \DateTimeImmutable('now');
        }

        $query = $this->createQueryBuilder('f')
            ->where('f.expirationDate < ?1')
            ->setParameter(1, $nowDate->format('Y-m-d H:i:s'))
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @inheritdoc
     */
    public function getFilesForJsonApi(\DateTimeInterface $createdAfter = null)
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->select('f.fileId, f.name, f.checksum, f.size, f.type, f.expirationDate, f.complete')
            ->orderBy('f.id', 'DESC');

        if (null !== $createdAfter) {
            $queryBuilder
                ->where('f.createdAt > ?1')
                ->setParameter(1, $createdAfter->format('Y-m-d H:i:s'));
        }

        $query = $queryBuilder->getQuery();

        return $query->getArrayResult();
    }

    /**
     * @inheritdoc
     */
    public function findOneByShortIdentifier($shortIdentifier)
    {
        $queryBuilder = $this->createQueryBuilder('f');
        $queryBuilder
            ->where($queryBuilder->expr()->eq('f.fileId', ':fileId'))
            ->setParameter('fileId', $shortIdentifier);

        return $queryBuilder->getQuery()->getSingleResult();
    }

    /**
     * @inheritdoc
     */
    public function findOneByNameAndChecksumAndSize($fileName, $fileChecksum, $fileSize)
    {
        $queryBuilder = $this->createQueryBuilder('f');
        $queryBuilder
            ->where($queryBuilder->expr()->eq('f.name', ':fileName'))
            ->setParameter('fileName', $fileName)
            ->andWhere($queryBuilder->expr()->eq('f.checksum', ':fileChecksum'))
            ->setParameter('fileChecksum', $fileChecksum)
            ->andWhere($queryBuilder->expr()->eq('f.size', ':fileSize'))
            ->setParameter('fileSize', $fileSize);

        try {
            $result = $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            $result = null;
            // Add logger
        } catch (NonUniqueResultException $e) {
            $result = null;
            // Add logger
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function remove(File $file)
    {
        try {
            $this->getEntityManager()->remove($file);
        } catch (ORMInvalidArgumentException $invalidArgumentException) {
            throw new InvalidEntityException(
                $invalidArgumentException->getMessage(),
                $invalidArgumentException->getCode(),
                $invalidArgumentException
            );
        }
        try {
            $this->getEntityManager()->flush();
        } catch (DoctrineOptimisticLockException $optimisticLockException) {
            throw new OptimisticLockException(
                $optimisticLockException->getMessage(),
                $optimisticLockException->getCode(),
                $optimisticLockException
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function persist(File $file)
    {
        try {
            $this->getEntityManager()->persist($file);
        } catch (ORMInvalidArgumentException $invalidArgumentException) {
            throw new InvalidEntityException(
                $invalidArgumentException->getMessage(),
                $invalidArgumentException->getCode(),
                $invalidArgumentException
            );
        }
        try {
            $this->getEntityManager()->flush();
        } catch (DoctrineOptimisticLockException $optimisticLockException) {
            throw new OptimisticLockException(
                $optimisticLockException->getMessage(),
                $optimisticLockException->getCode(),
                $optimisticLockException
            );
        }
    }
}
