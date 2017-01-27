<?php
namespace Enginewerk\FileManagementBundle\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Enginewerk\FileManagementBundle\Entity\FileBlock;
use Enginewerk\FileManagementBundle\Repository\FileBlockRepositoryInterface;

class FileBlockRepository extends EntityRepository implements FileBlockRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function getUsedBlocksNumber($fileHash)
    {
        $queryBuilder = $this->createQueryBuilder('fb');

        $queryBuilder->select('COUNT(fb.id) AS totalNumber')
            ->where($queryBuilder->expr()->eq('fb.fileHash', ':fileHash'))
            ->setParameter(':fileHash', $fileHash);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @inheritdoc
     */
    public function getTotalSize($fileId)
    {
        $queryBuilder = $this->createQueryBuilder('fb');
        $queryBuilder->select('SUM(fb.size) AS totalSize')
            ->where($queryBuilder->expr()->eq('fb.file', ':fileId'))
            ->setParameter('fileId', $fileId);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @inheritdoc
     */
    public function findByFileId($fileId)
    {
        $queryBuilder = $this->createQueryBuilder('fb');
        $queryBuilder
            ->where($queryBuilder->expr()->eq('fb.file', ':fileId'))
            ->setParameter('fileId', $fileId)
            ->orderBy('fb.rangeStart', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findByFileIdAndRangeStartAndRangeEnd($fileId, $rangeStart, $rangeEnd)
    {
        $queryBuilder = $this->createQueryBuilder('fb');
        $queryBuilder
            ->where($queryBuilder->expr()->eq('fb.file', ':fileId'))
            ->setParameter('fileId', $fileId)
            ->andWhere($queryBuilder->expr()->eq('fb.rangeStart', ':rangeStart'))
            ->setParameter('rangeStart', $rangeStart)
            ->andWhere($queryBuilder->expr()->eq('fb.rangeEnd', ':rangeEnd'))
            ->setParameter('rangeEnd', $rangeEnd);

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
    public function remove(FileBlock $fileBlock)
    {
        $this->getEntityManager()->remove($fileBlock);
        $this->getEntityManager()->flush();
    }

    /**
     * @inheritdoc
     */
    public function persist(FileBlock $fileBlock)
    {
        $this->getEntityManager()->persist($fileBlock);
        $this->getEntityManager()->flush();
    }
}
