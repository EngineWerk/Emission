<?php
namespace Enginewerk\UserBundle\Entity;

class Invitation
{
    protected $code;

    protected $email;

    /**
     * When sending invitation be sure to set this value to `true`
     *
     * It can prevent invitations from being sent twice
     */
    protected $sent = false;

    /**
     * @var User
     */
    protected $user;

    public function __construct()
    {
        // generate identifier only once, here a 6 characters length code
        $this->code = substr(md5(uniqid(rand(), true)), 0, 6);
    }

    /**
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
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
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
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
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
     * @return bool
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * @param  User $user
     *
     * @return Invitation
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
