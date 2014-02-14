<?php

namespace Enginewerk\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Enginewerk\UserBundle\Entity\Invitation;

/**
 * Adds User invitation code to database
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class InvitationAddCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('invitation:add')
            ->setDescription('Adds User invitation code to database')
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Adding ' . $input->getArgument('email'));

        $invitation = new Invitation();
        $invitation->setEmail($input->getArgument('email'));

        $validator = $this->getContainer()->get('validator');
        $errors = $validator->validate($invitation);

        if (count($errors)) {
            throw new \RuntimeException((string) $errors);
        }

        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($invitation);

        try {
            $em->flush();
            $output->writeln(' with code: ' . $invitation->getCode());
        } catch (Exception $e) {
            $output->writeln(' faild: ' . $e->getMessage());
        }
    }
}
