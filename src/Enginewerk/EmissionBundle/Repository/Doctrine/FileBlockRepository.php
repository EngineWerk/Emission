<?php
namespace Enginewerk\EmissionBundle\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use Enginewerk\EmissionBundle\Entity\FileBlock;
use Enginewerk\EmissionBundle\Repository\FileBlockRepositoryInterface;

class FileBlockRepository extends EntityRepository implements FileBlockRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function finOneById($fileId, $rangeStart, $rangeEnd)
    {
        $queryBuilder = $this->createQueryBuilder('fb');
        $queryBuilder
            ->where($queryBuilder->expr()->eq('fb.id', ':id'))
            ->setParameter('id', $fileId)
            ->andWhere($queryBuilder->expr()->eq('fb.rangeStart', ':rangeStart'))
            ->setParameter('rangeStart', $rangeStart)
            ->andWhere($queryBuilder->expr()->eq('fb.rangeEnd', ':rangeEnd'))
            ->setParameter('rangeEnd', $rangeEnd);

        return $queryBuilder->getQuery()->getFirstResult();
    }

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
    public function remove(FileBlock $fileBlock)
    {
        $this->getEntityManager()->remove($fileBlock);
        $this->getEntityManager()->flush();
    }
}
