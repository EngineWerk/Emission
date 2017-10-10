<?php
namespace Enginewerk\UserBundle\Repository;

use Enginewerk\ApplicationBundle\Repository\NoResultException;
use Enginewerk\UserBundle\Entity\User;

interface UserFinderInterface
{
    /**
     * @param string $email
     *
     * @return User|null
     */
    public function findByEmail($email);

    /**
     * @param string $email
     *
     * @throws NoResultException
     *
     * @return User
     */
    public function getByEmail($email);
}
