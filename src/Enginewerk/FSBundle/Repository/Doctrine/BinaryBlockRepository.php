<?php
namespace Enginewerk\FSBundle\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use Enginewerk\FSBundle\Entity\BinaryBlock;
use Enginewerk\FSBundle\Repository\BinaryBlockRepositoryInterface;

class BinaryBlockRepository extends EntityRepository implements BinaryBlockRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function removeBlockByChecksum($checksum)
    {
        $block = $this->findOneByChecksum($checksum);

        if ($block) {
            $this->getEntityManager()->remove($block);
        }
    }

    /**
     * @inheritdoc
     */
    public function persist(BinaryBlock $binaryBlock)
    {
        $this->getEntityManager()->persist($binaryBlock);
    }

    /**
     * @inheritdoc
     */
    public function findOneByUniformResourceName($urn)
    {
        $queryBuilder = $this->createQueryBuilder('bb');
        $queryBuilder
            ->where($queryBuilder->expr()->eq('bb.urn', ':urn'))
            ->setParameter('urn', $urn);

        return $queryBuilder->getQuery()->getSingleResult();
    }

    /**
     * @inheritdoc
     */
    public function remove(BinaryBlock $binaryBlock)
    {
        $this->getEntityManager()->remove($binaryBlock);
        $this->getEntityManager()->flush();
    }
}
