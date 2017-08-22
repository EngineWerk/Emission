<?php
namespace Enginewerk\UserBundle\Command;

use Enginewerk\UserBundle\Entity\Invitation;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes User invitation
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class InvitationRemoveCommand extends ContainerAwareCommand
{
    protected $forceRemove = null;

    protected function configure()
    {
        $this
            ->setName('invitation:remove')
            ->setDescription('Removes not sent User invitation from database')
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addOption('code', null, InputOption::VALUE_OPTIONAL, 'User invitation code')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force remove');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->forceRemove = ($input->getOption('force')) ? true : false;

        $output->write('Removing for "' . $input->getArgument('email') . '"');

        $repository = $this
                ->getEntityManager()
                ->getRepository('UserBundle:Invitation');

        if ($input->getOption('code')) {
            $invitations = $repository->findByEmailAndCode($input->getArgument('email'), $input->getOption('code'));
        } else {
            $invitations = $repository->findByEmail($input->getArgument('email'));
        }

        if ($invitations) {
            $this->removeInvitations($invitations, $output);
        } else {
            $output->writeln(' faild...no match');
        }
    }

    protected function removeInvitation(Invitation $invitation)
    {
        if ($invitation->getSent() === true && $this->forceRemove === false) {
            throw new Exception('Invitation state is "sent" and therefore cannot be removed');
        }

        $this
                ->getEntityManager()
                ->remove($invitation);
        $this
                ->getEntityManager()
                ->flush();
    }

    protected function removeInvitations($invitations, OutputInterface $output)
    {
        foreach ($invitations as $invitation) {
            $output->write(' Trying: ' . $invitation->getCode());

            try {
                $this->removeInvitation($invitation);
                $output->writeln(' success');
            } catch (Exception $e) {
                $output->writeln(' faild: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get a doctrine entity manager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this
                ->getContainer()
                ->get('doctrine')
                ->getManager();
    }
}
