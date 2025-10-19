<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Repository\VideoGameRepository;
use App\Scheduler\Message\SendEmailMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use App\Scheduler\Service\MailerService;
use Symfony\Component\Mime\Email;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCommand(
    name: 'app:send-newsletter',
    description: 'Envoie un mail à tous les utilisateurs abonnés à la newsletter',
)]
#[AsCronTask('30 8 * * 1')]
class SendNewsletterCommand extends Command
{
    private UserRepository $userRepository;
    private VideoGameRepository $videoGameRepository;
    private MailerService $mailerService;

    public function __construct(UserRepository $userRepository, VideoGameRepository $videoGameRepository, MailerService $mailerService)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->videoGameRepository = $videoGameRepository;
        $this->mailerService = $mailerService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $subscribedUsers = $this->userRepository->findBy(['subscription_to_newsletter' => true]);

        $now = new \DateTime();
        $endDate = (new \DateTime())->add(new \DateInterval('P7D'));
        $upcomingGames = $this->videoGameRepository->findByReleaseDate($now, $endDate);

        foreach ($subscribedUsers as $user) {
            $this->mailerService->sendEmail(
                $user->getEmail(),
                'Newsletter - Nouveaux jeux vidéo à venir',
                'emails/newsletter.twig',
                ['upcomingGames' => $upcomingGames]
            );
            usleep(1000000);
            $output->writeln('envoyé');
        }
        $output->writeln('Les emails ont été envoyés.');

        return Command::SUCCESS;
    }
}
