<?php
namespace Enginewerk\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Enginewerk\EmissionBundle\Entity\File as FileEntity;
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
     * @param Invitation $invitation
     *
     * @return User
     */
    public function setInvitation(Invitation $invitation = null)
    {
        $this->invitation = $invitation;

        return $this;
    }

    /**
     * Get invitation
     *
     * @return Invitation
     */
    public function getInvitation()
    {
        return $this->invitation;
    }

    /**
     * Add files
     *
     * @param FileEntity $files
     *
     * @return User
     */
    public function addFile(FileEntity $files)
    {
        $this->files[] = $files;

        return $this;
    }

    /**
     * Remove files
     *
     * @param FileEntity $files
     */
    public function removeFile(FileEntity $files)
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
