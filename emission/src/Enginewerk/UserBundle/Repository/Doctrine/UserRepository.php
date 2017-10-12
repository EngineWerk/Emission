<?php
namespace Enginewerk\UserBundle\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException as DoctrineNoResultException;
use Enginewerk\ApplicationBundle\Repository\NoResultException;
use Enginewerk\UserBundle\Entity\User;
use Enginewerk\UserBundle\Repository\UserFinderInterface;

class UserRepository extends EntityRepository implements UserFinderInterface
{
    /**
     * @inheritdoc
     */
    public function findByEmail($email)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->where($queryBuilder->expr()->eq('u.emailCanonical', ':email'));
        $queryBuilder->setParameter('email', $email);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getByEmail($email)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->where($queryBuilder->expr()->eq('u.emailCanonical', ':email'));
        $queryBuilder->setParameter('email', $email);

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (DoctrineNoResultException $noResultException) {
            throw new NoResultException(sprintf('Expected "%s" entity, got none', User::class));
        }
    }
}
