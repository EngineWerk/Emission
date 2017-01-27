<?php
namespace Enginewerk\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
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
     * @ORM\JoinColumn(name="invitation_id", referencedColumnName="code")
     * @Assert\NotNull(message="Your invitation is wrong")
     */
    protected $invitation;

    /**
     * @ORM\OneToMany(targetEntity="Enginewerk\FileManagementBundle\Entity\File", mappedBy="user", cascade={"remove"})
     */
    protected $files;

    public function __construct()
    {
        parent::__construct();

        $this->files = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set google
     *
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
     * @param \Enginewerk\FileManagementBundle\Entity\File $files
     *
     * @return User
     */
    public function addFile(\Enginewerk\FileManagementBundle\Entity\File $files)
    {
        $this->files[] = $files;

        return $this;
    }

    /**
     * Remove files
     *
     * @param \Enginewerk\FileManagementBundle\Entity\File $files
     */
    public function removeFile(\Enginewerk\FileManagementBundle\Entity\File $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFiles()
    {
        return $this->files;
    }
}
