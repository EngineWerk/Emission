<?php
namespace Enginewerk\Shared\Infrastructure\Persistence\Doctrine\Sf3DoctrineMigrationBundle\Helper;

class MigrationGeneratorHelper
{
    /**
     * @param string $originalVersion
     * @param string $feature alpha-numeric and _ and -
     *
     * @return string
     */
    public static function generateMigrationVersionWithTicketNumber($originalVersion, $feature)
    {
        $feature = str_replace('-', '_', $feature);
        if (preg_match('/[^\w]+/', $feature)) {
            throw new \InvalidArgumentException('Only alpha-numeric and _ are allowed.');
        }

        return $originalVersion . '_' . $feature;
    }
}
