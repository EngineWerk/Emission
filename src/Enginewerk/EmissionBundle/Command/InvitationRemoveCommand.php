<?php

namespace Enginewerk\EmissionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes User invitation
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class InvitationRemoveCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('invitation:remove')
            ->setDescription('Removes not sent User invitation from database')
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addOption('code', null, InputOption::VALUE_OPTIONAL, 'User invitation code')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Removing for "' . $input->getArgument('email') . '"');

        $em = $this->getContainer()->get('doctrine')->getManager();
        $repository = $this->getContainer()->get('doctrine')->getRepository('UserBundle:Invitation');

        if ($input->getOption('code')) {
            $invitations = $repository->findOneByEmailAndCodeAndSent($input->getArgument('email'), $input->getOption('code'), false);
        } else {
            $invitations = $repository->findBy(array('sent' => false, 'email' => $input->getArgument('email')));
        }

        $invitationNumber = count($invitations);

        if ($invitationNumber) {

            if ($invitationNumber > 1) {
                foreach ($invitations as $invitation) {
                    $em->remove($invitation);
                }

                $output->write(' ' . $invitationNumber);
                $output->write(' occurences');
            } else {
                $em->remove(array_pop($invitations));
                $output->write(' ' . $invitationNumber);
                $output->write(' occurence');
            }

            try {
                $em->flush();
                $output->writeln(' success');
            } catch (Exception $e) {
                $output->writeln(' faild: ' . $e->getMessage());
            }

        } else {
            $output->writeln(' faild...no match');
        }
    }
}
