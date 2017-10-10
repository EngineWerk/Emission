<?php
namespace Enginewerk\EmissionBundle\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException as DoctrineNoResultException;
use Enginewerk\ApplicationBundle\Repository\NoResultException;
use Enginewerk\EmissionBundle\Entity\File as FileEntity;
use Enginewerk\EmissionBundle\Repository\FileRepositoryInterface;

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
    public function findAllAsArray(\DateTimeInterface $createdAfter = null)
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->select('f.publicIdentifier, f.name, f.checksum, f.size, f.type, f.expirationDate, f.complete')
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
    public function findByPublicIdentifier($publicIdentifier)
    {
        $queryBuilder = $this->createQueryBuilder('f');
        $queryBuilder
            ->where($queryBuilder->expr()->eq('f.publicIdentifier', ':publicIdentifier'))
            ->setParameter('publicIdentifier', $publicIdentifier);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /** @inheritdoc */
    public function getByPublicIdentifier($publicIdentifier)
    {
        $queryBuilder = $this->createQueryBuilder('f');
        $queryBuilder
            ->where($queryBuilder->expr()->eq('f.publicIdentifier', ':publicIdentifier'))
            ->setParameter('publicIdentifier', $publicIdentifier);

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (DoctrineNoResultException $noResultException) {
            throw new NoResultException('Expected entity, got none');
        }
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
            $result = $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            $result = null;
            // Add logger
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function remove(FileEntity $file)
    {
        $this->getEntityManager()->remove($file);
        $this->getEntityManager()->flush();
    }

    /**
     * @inheritdoc
     */
    public function persist(FileEntity $file)
    {
        $this->getEntityManager()->persist($file);
        $this->getEntityManager()->flush();
    }
}
