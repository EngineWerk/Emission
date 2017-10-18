<?php
namespace Enginewerk\EmissionBundle\Generator;

interface PublicIdentifierGeneratorInterface
{
    /**
     * @param int $length
     *
     * @return string
     */
    public function generate($length = 8);
}
