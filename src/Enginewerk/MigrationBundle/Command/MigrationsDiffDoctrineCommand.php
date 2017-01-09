<?php
namespace Enginewerk\MigrationBundle\Command;

use Doctrine\Bundle\MigrationsBundle\Command\MigrationsDiffDoctrineCommand as BaseMigrationsDiffDoctrineCommand;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Enginewerk\MigrationBundle\Helper\MigrationGeneratorHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MigrationsDiffDoctrineCommand extends BaseMigrationsDiffDoctrineCommand
{
    protected function configure()
    {
        parent::configure();

        $this->addArgument('feature', InputArgument::REQUIRED, 'Feature ID');
    }

    /**
     * @param Configuration $configuration
     * @param InputInterface $input
     * @param string $version
     * @param null|string $up
     * @param null|string $down
     *
     * @return string
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
