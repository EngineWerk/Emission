<?php
namespace Enginewerk\EmissionBundle\Command;

use Enginewerk\EmissionBundle\Presentation\Model\FileView;
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

        $fileManager = $this->getContainer()->get('enginewerk_emission.storage.file_manager');
        $fileViewCollection = $this->getContainer()->get('enginewerk_emission.service.file_presentation_service')->findExpiredFiles($date);

        /** @var FileView $fileView */
        foreach ($fileViewCollection as $fileView) {
            $output->writeln($fileView->getName());
            try {
                $fileManager->delete($fileView->getFileId());
            } catch (\Exception $e) {
                $this->getContainer()
                    ->get('logger')
                    ->error(sprintf('Can`t remove File #%s. %s', $fileView->getFileId(), $e->getMessage()));
            }
        }
    }
}
