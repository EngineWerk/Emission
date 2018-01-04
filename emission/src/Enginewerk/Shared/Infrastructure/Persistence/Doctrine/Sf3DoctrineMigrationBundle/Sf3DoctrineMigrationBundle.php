<?php
namespace Enginewerk\Shared\Infrastructure\Persistence\Doctrine\Sf3DoctrineMigrationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Sf3DoctrineMigrationBundle extends Bundle
{
    /**
     * @return string
     */
    public function getParent()
    {
        return 'DoctrineMigrationsBundle';
    }
}
