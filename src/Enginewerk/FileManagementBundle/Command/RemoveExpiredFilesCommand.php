<?php
namespace Enginewerk\FileManagementBundle\Command;

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

        $fileStorage = $this->getContainer()->get('enginewerk_emission.storage.file_storage');

        $expiredFiles = $this->getContainer()
            ->get('enginewerk_emission.service.file_read_service')
            ->getExpiredFiles(); // TODO remove direct usage

        foreach ($expiredFiles as $file) {
            $output->writeln($file->getName());
            try {
                $fileStorage->deleteFile($file->getFileId());
            } catch (\Exception $e) {
                $this->getContainer()
                    ->get('logger')
                    ->error(sprintf('Can`t remove File #%s. %s', $file->getFileId(), $e->getMessage()));
            }
        }
    }
}
