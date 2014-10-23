<?php

namespace Enginewerk\EmissionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of RemoveExpiredFilesCommand
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class RemoveExpiredFilesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('emission:remove:expired-files')
            ->setDescription('Removes expired files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime('now');
        $output->writeln($date->format('Y-m-d H:i:s'));

        $efs = $this->getContainer()->get('emission_file_storage');
        /* @var $efs \Enginewerk\EmissionBundle\Storage\FileStorage */

        $repository = $this
                ->getContainer()
                ->get('doctrine')
                ->getRepository('EnginewerkEmissionBundle:File');

        $files = $repository->getExpiredFiles();

        foreach ($files as $file) {
            /* @var $file \Enginewerk\EmissionBundle\Entity\File */
            $output->writeln($file->getName());
            try {
                $efs->delete($file->getFileId());
            } catch (\Exception $e) {
                $this
                        ->getContainer()
                        ->get('logger')
                        ->error(sprintf('Can`t remove File #%s. %s', $file->getFileId(), $e->getMessage()));
            }
        }
    }
}
