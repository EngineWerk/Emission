<?php
namespace Enginewerk\EmissionBundle\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use Enginewerk\EmissionBundle\Entity\File;
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
     * @param File $file
     */
    public function update(File $file)
    {
        $this->getEntityManager()->refresh($file);
    }

    /**
     * @inheritdoc
     */
    public function remove(File $file)
    {
        $this->getEntityManager()->remove($file);
        $this->getEntityManager()->flush();
    }
}
