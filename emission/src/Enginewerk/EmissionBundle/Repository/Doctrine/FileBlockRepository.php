<?php
namespace Enginewerk\EmissionBundle\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Enginewerk\EmissionBundle\Entity\File;
use Enginewerk\EmissionBundle\Entity\FileBlock;
use Enginewerk\EmissionBundle\Repository\FileBlockRepositoryInterface;

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
    public function getTotalSize($publicIdentifier)
    {
        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('SUM(fb.size) AS totalSize')
            ->from(File::class, 'f');

        $queryBuilder->leftJoin('f.fileBlocks', 'fb')
            ->where($queryBuilder->expr()->eq('f.publicIdentifier', ':publicIdentifier'))
            ->setParameter('publicIdentifier', $publicIdentifier);

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
    public function findByFileIdAndRangeStartAndRangeEnd($publicIdentifier, $rangeStart, $rangeEnd)
    {
        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('f')
            ->from(File::class, 'f');

        $queryBuilder
            ->leftJoin('f.fileBlocks', 'fb')
            ->where($queryBuilder->expr()->eq('f.publicIdentifier', ':publicIdentifier'))
            ->setParameter('publicIdentifier', $publicIdentifier)
            ->andWhere($queryBuilder->expr()->eq('fb.rangeStart', ':rangeStart'))
            ->setParameter('rangeStart', $rangeStart)
            ->andWhere($queryBuilder->expr()->eq('fb.rangeEnd', ':rangeEnd'))
            ->setParameter('rangeEnd', $rangeEnd);

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
