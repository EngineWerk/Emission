<?php
namespace Enginewerk\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

class User extends BaseUser
{
    protected $google;

    /**
     * @Assert\NotNull(message="Your invitation is wrong")
     */
    protected $invitation;

    /**
     * @var ArrayCollection
     */
    protected $files;

    public function __construct()
    {
        parent::__construct();

        $this->files = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  string $google
     *
     * @return User
     */
    public function setGoogle($google)
    {
        $this->google = $google;

        return $this;
    }

    /**
     * @return string
     */
    public function getGoogle()
    {
        return $this->google;
    }

    /**
     * @param  \Enginewerk\UserBundle\Entity\Invitation $invitation
     *
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

    /**
     * Add files
     *
     * @param \Enginewerk\EmissionBundle\Entity\File $files
     *
     * @return User
     */
    public function addFile(\Enginewerk\EmissionBundle\Entity\File $files)
    {
        $this->files[] = $files;

        return $this;
    }

    /**
     * Remove files
     *
     * @param \Enginewerk\EmissionBundle\Entity\File $files
     */
    public function removeFile(\Enginewerk\EmissionBundle\Entity\File $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }
}
