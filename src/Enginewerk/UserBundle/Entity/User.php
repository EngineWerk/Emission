<?php

namespace Enginewerk\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of User
 *
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

     /**
      * @ORM\Column(name="google", type="string", length=255, nullable=true)
      */
    protected $google;

    /**
     * @ORM\OneToOne(targetEntity="Invitation", inversedBy="user")
     * @ORM\JoinColumn(referencedColumnName="code")
     * @Assert\NotNull(message="Your invitation is wrong")
     */
    protected $invitation;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set google
     *
     * @param  string $google
     * @return User
     */
    public function setGoogle($google)
    {
        $this->google = $google;

        return $this;
    }

    /**
     * Get google
     *
     * @return string
     */
    public function getGoogle()
    {
        return $this->google;
    }

    /**
     * Set invitation
     *
     * @param  \Enginewerk\UserBundle\Entity\Invitation $invitation
     * @return User
     */
    public function setInvitation(\Enginewerk\UserBundle\Entity\Invitation $invitation = null)
    {
        $this->invitation = $invitation;

        return $this;
    }

    /**
     * Get invitation
     *
     * @return \Enginewerk\UserBundle\Entity\Invitation
     */
    public function getInvitation()
    {
        return $this->invitation;
    }
}
