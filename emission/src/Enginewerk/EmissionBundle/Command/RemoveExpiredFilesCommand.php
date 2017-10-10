<?php
namespace Enginewerk\EmissionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveExpiredFilesCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('emission:remove:expired-files')
            ->setDescription('Removes expired files');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTimeImmutable('now');
        $output->writeln($date->format('Y-m-d H:i:s'));

        $efs = $this->getContainer()->get('enginewerk_emission.storage.file_storage');
        $files = $this->getContainer()->get('enginewerk_emission.service.file_read_service')->getExpiredFiles();

        foreach ($files as $file) {
            $output->writeln($file->getName());
            try {
                $efs->delete($file->getPublicIdentifier());
            } catch (\Exception $e) {
                $this->getContainer()
                    ->get('logger')
                    ->error(sprintf('Can`t remove File #%s. %s', $file->getPublicIdentifier(), $e->getMessage()));
            }
        }
    }
}
