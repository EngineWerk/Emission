<?php

namespace Enginewerk\EmissionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates database table for session storage
 * @link http://symfony.com/doc/current/cookbook/configuration/pdo_session_storage.html#example-sql-statements
 * @package Enginewerk\EmissionBundle\Command
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class SessionStorageCommand extends ContainerAwareCommand
{
    protected static $supportedDatabases = array('mysql', 'mssql', 'postgresql');

    protected function configure()
    {
        $this
            ->setName('sessionstorage:init')
            ->setDescription('Creates database table for session storage')
            ->addArgument('dbengine', InputArgument::OPTIONAL, 'Database engine [mysql, mssql, postgresql].', null)
            ->setHelp(<<<'EOD'
Try using sessionstorage:init [mysql|mssql|postgresql]
<info>If there is no argument script will try to guess database based on database_driver</info>
EOD
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbengine = ($input->getArgument('dbengine')) ? $input->getArgument('dbengine') : $this->guessDatabseEngine();

        $output->writeln('Creating datbase table for session storage "' . $dbengine . '"');

        switch ($dbengine) {
            case 'mysql':
                $sql = $this->getMysqlStatement();
                break;
            case 'postgresql':
                $sql = $this->getPostgresqlStatement();
                break;
            case 'mssql':
                $sql = $this->getMssqlStatement();
                break;
            default:
                throw new \RuntimeException(
                    sprintf(
                        'Unsupported dbengine option "%s". Available values: %s',
                        $dbengine,
                        implode(',', self::$supportedDatabases)
                    )
                );
                break;
        }

        $stmt = $this
            ->getContainer()
            ->get('doctrine')
            ->getConnection()
            ->prepare($sql);

        try {
            $stmt->execute();
        } catch (\Exception $e) {
            $this
                ->getContainer()
                ->get('logger')
                ->error('Can`t create table. ' . $e->getMessage());
        }

    }

    private function getMysqlStatement()
    {
        return <<<'EOD'
CREATE TABLE `session` (
    `session_id` varchar(255) NOT NULL,
    `session_value` text NOT NULL,
    `session_time` int(11) NOT NULL,
    PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;

    }

    private function getPostgresqlStatement()
    {
        return <<<'EOD'
CREATE TABLE session (
    session_id character varying(255) NOT NULL,
    session_value text NOT NULL,
    session_time integer NOT NULL,
    CONSTRAINT session_pkey PRIMARY KEY (session_id)
);
EOD;
    }

    private function getMssqlStatement()
    {
        return <<<'EOD'
CREATE TABLE [dbo].[session](
    [session_id] [nvarchar](255) NOT NULL,
    [session_value] [ntext] NOT NULL,
    [session_time] [int] NOT NULL,
    PRIMARY KEY CLUSTERED(
        [session_id] ASC
    ) WITH (
        PAD_INDEX  = OFF,
        STATISTICS_NORECOMPUTE  = OFF,
        IGNORE_DUP_KEY = OFF,
        ALLOW_ROW_LOCKS  = ON,
        ALLOW_PAGE_LOCKS  = ON
    ) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
EOD;
    }

    private function guessDatabseEngine()
    {
        $config = $this->getContainer()->getParameter('database_driver');
        $dbengine = substr($config, strpos($config, '_') + 1);

        return $dbengine;
    }
}
