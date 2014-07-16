<?php

namespace Enginewerk\FSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BinaryBlock
 *
 * @ORM\Table(name="binary_block")
 * @ORM\Entity(repositoryClass="Enginewerk\FSBundle\Entity\BinaryBlockRepository")
 */
class BinaryBlock
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string Uniform Resource Name - Name / Key / Pathname / URL
     *
     * @ORM\Column(name="urn", type="string", length=255)
     */
    protected $urn;

    /**
     * @var string
     *
     * @ORM\Column(name="checksum", type="string", length=40)
     */
    private $checksum;

    /**
     * @var integer
     *
     * @ORM\Column(name="size", type="integer")
     */
    private $size;

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
     * Set urn
     *
     * @param string $urn
     * @return BinryBlock
     */
    public function setUrn($urn)
    {
        $this->urn = $urn;

        return $this;
    }

    /**
     * Get urn
     *
     * @return string 
     */
    public function getUrn()
    {
        return $this->urn;
    }

    /**
     * Set checksum
     *
     * @param  string      $checksum
     * @return BinaryBlock
     */
    public function setChecksum($checksum)
    {
        $this->checksum = $checksum;

        return $this;
    }

    /**
     * Get checksum
     *
     * @return string
     */
    public function getChecksum()
    {
        return $this->checksum;
    }

    /**
     * Set size
     *
     * @param  integer     $size
     * @return BinaryBlock
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }
}
