<?php
namespace Enginewerk\MigrationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class EmissionMigrationBundle extends Bundle
{
    /**
     * @return string
     */
    public function getParent()
    {
        return 'DoctrineMigrationsBundle';
    }
}
