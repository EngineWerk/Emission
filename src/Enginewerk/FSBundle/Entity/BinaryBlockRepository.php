<?php
namespace Enginewerk\FSBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BinaryBlockRepository extends EntityRepository
{
    /**
     * @param string $checksum
     */
    public function removeBlockByChecksum($checksum)
    {
        $block = $this->findOneByChecksum($checksum);
        if ($block) {
            $this->getEntityManager()->remove($block);
        }
    }
}
