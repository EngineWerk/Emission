<?php

namespace Enginewerk\EmissionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
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
            ->setName('emission:remove-expired')
            ->setDescription('Removes expired files')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime('now');
        $output->writeln($date->format('Y-m-d H:i:s'));
        
        $repository = $this->getContainer()->get('doctrine')->getRepository('EnginewerkEmissionBundle:File');
        $em = $this->getContainer()->get('doctrine')->getManager();        
        $files = $repository->getExpiredFiles();
        
        foreach($files as $file) {
            $output->writeln($file->getName());
            try{
                // Nie działa z usługą BBS
                $em->remove($file);
                $em->flush();
            } catch (\Exception $ex) {
                $this->getContainer()->get('logger')->error(sprintf('Can`t remove File #%s. %s', $file->getId(), $ex->getMessage()));
            }
        }
    }
}
