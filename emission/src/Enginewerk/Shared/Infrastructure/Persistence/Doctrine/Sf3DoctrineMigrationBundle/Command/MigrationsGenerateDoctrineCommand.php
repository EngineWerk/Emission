<?php
namespace Enginewerk\Shared\Infrastructure\Persistence\Doctrine\Sf3DoctrineMigrationBundle\Command;

use Doctrine\Bundle\MigrationsBundle\Command\MigrationsGenerateDoctrineCommand as BaseMigrationsGenerateDoctrineCommand;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Enginewerk\Shared\Infrastructure\Persistence\Doctrine\Sf3DoctrineMigrationBundle\Helper\MigrationGeneratorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MigrationsGenerateDoctrineCommand extends BaseMigrationsGenerateDoctrineCommand
{
    protected function configure()
    {
        parent::configure();

        $this->addArgument('feature', InputArgument::REQUIRED, 'Feature ID');
    }

    /**
     * @inheritdoc
     */
    protected function generateMigration(
        Configuration $configuration,
        InputInterface $input,
        $version,
        $up = null,
        $down = null
    ) {
        $version = MigrationGeneratorHelper::generateMigrationVersionWithTicketNumber(
            $version,
            $input->getArgument('feature')
        );

        return parent::generateMigration($configuration, $input, $version, $up, $down);
    }
}
