<?php

namespace Enginewerk\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * List User invitations
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class InvitationListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('invitation:list')
            ->setDescription('Lists user invitations')
            ->addOption('sent', null, InputOption::VALUE_NONE, 'User invitation code')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->getContainer()->get('doctrine')->getRepository('UserBundle:Invitation');

        if ($input->getOption('sent')) {
            $invitations = $repository->findBySent(true);
        } else {
            $invitations = $repository->findAll();
        }

        foreach ($invitations as $invitation) {
            $output->write($invitation->getCode() . ' ');
            $output->write($invitation->getEmail());
            $output->writeln(($invitation->getSent()) ? ' sent' : ' -');
        }
    }
}
