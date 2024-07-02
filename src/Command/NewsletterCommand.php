<?php


namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Console\Input\InputOption;

class NewsletterCommand extends Command
{
    protected static $defaultName = 'app:send-newsletter-email';
    private $userRepository;
    private $mailer;

    /**
     * @param UserRepository $userRepository
     * @param MailerInterface $mailer
     */
    public function __construct(UserRepository $userRepository, MailerInterface $mailer)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Send a unique email to all active users created during the last week.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Perform a dry run without sending emails');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dryRun = $input->getOption('dry-run');

        $oneWeekAgo = new \DateTime('-1 week');
        $activeUsers = $this->userRepository->findActiveUsersCreatedSince($oneWeekAgo);

        foreach ($activeUsers as $user) {
            $email = (new Email())
                ->from('no-reply@cobbleweb.com')
                ->to($user->getEmail())
                ->subject('Your best newsletter')
                ->text('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec id interdum nibh. Phasellus blandit tortor in cursus convallis. Praesent et tellus fermentum, pellentesque lectus at, tincidunt risus. Quisque in nisl malesuada, aliquet nibh at, molestie libero.')
                ->html('<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec id interdum nibh. Phasellus blandit tortor in cursus convallis. Praesent et tellus fermentum, pellentesque lectus at, tincidunt risus. Quisque in nisl malesuada, aliquet nibh at, molestie libero.</p>');

            if (!$dryRun) {
                $this->mailer->send($email);
            }

            $output->writeln(sprintf('Email sent to %s', $user->getEmail()));
        }

        return Command::SUCCESS;
    }
}
