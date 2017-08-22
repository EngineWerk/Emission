<?php
namespace Enginewerk\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of Invitation
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="invitation")
 */
class Invitation
{
    /**
     * @ORM\Id @ORM\Column(type="string", length=6)
     */
    protected $code;

    /**
     * @ORM\Column(type="string", length=256)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = false
     * )
     */
    protected $email;

    /**
     * When sending invitation be sure to set this value to `true`
     *
     * It can prevent invitations from being sent twice
     *
     * @ORM\Column(type="boolean")
     */
    protected $sent = false;

    /**
     * @ORM\OneToOne(targetEntity="User", mappedBy="invitation", cascade={"persist", "merge"})
     */
    protected $user;

    public function __construct()
    {
        // generate identifier only once, here a 6 characters length code
        $this->code = substr(md5(uniqid(rand(), true)), 0, 6);
    }

    /**
     * Set code
     *
     * @param  string     $code
     *
     * @return Invitation
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set email
     *
     * @param  string     $email
     *
     * @return Invitation
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set sent
     *
     * @param  bool    $sent
     *
     * @return Invitation
     */
    public function setSent($sent)
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * Get sent
     *
     * @return bool
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Set user
     *
     * @param  \Enginewerk\UserBundle\Entity\User $user
     *
     * @return Invitation
     */
    public function setUser(\Enginewerk\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Enginewerk\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
