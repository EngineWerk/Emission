<?php
namespace Enginewerk\EmissionBundle\Generator\Adapter;

use Enginewerk\EmissionBundle\Generator\HashGenerator;
use Enginewerk\EmissionBundle\Generator\PublicIdentifierGeneratorInterface;

final class NativeGeneratorAdapter implements PublicIdentifierGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate($length = 8)
    {
        return HashGenerator::generateRandomHash($length);
    }
}
