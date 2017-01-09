<?php
namespace Enginewerk\FSBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * BinaryBlockRepository.
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class BinaryBlockRepository extends EntityRepository
{
    public function removeBlockByChecksum($checksum)
    {
        $block = $this->findOneByChecksum($checksum);
        if ($block) {
            $this->getEntityManager()->remove($block);
        }
    }
}
