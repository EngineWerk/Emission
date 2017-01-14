<?php
namespace Enginewerk\FSBundle\Repository;

use Enginewerk\FSBundle\Entity\BinaryBlock;

interface BinaryBlockRepositoryInterface
{
    /**
     * @param string $checksum
     *
     * @return void
     */
    public function removeBlockByChecksum($checksum);

    /**
     * @param BinaryBlock $binaryBlock
     *
     * @return void
     */
    public function persist(BinaryBlock $binaryBlock);

    /**
     * @param string $urn
     *
     * @return BinaryBlock
     */
    public function findOneByUniformResourceName($urn);

    /**
     * @param BinaryBlock $binaryBlock
     */
    public function remove(BinaryBlock $binaryBlock);
}
