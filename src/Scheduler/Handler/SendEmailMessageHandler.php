<?php

namespace App\Scheduler\Handler;

use App\Scheduler\Message\SendEmailMessage;
use App\Scheduler\Service\MailerService;
use App\Repository\UserRepository;
use App\Repository\VideoGameRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Twig\Environment;
use Symfony\Component\Messenger\Attribute\MessageHandlerInterface;

#[AsMessageHandler]
class SendEmailMessageHandler{
    private MailerService $mailerService;
    private VideoGameRepository $videoGameRepository;
    private UserRepository $userRepository;
    private Environment $twig;
    public function __construct(MailerService $mailerService, VideoGameRepository $videoGameRepository, Environment $twig, UserRepository $userRepository)
    {
        $this->mailerService = $mailerService;
        $this->videoGameRepository = $videoGameRepository;
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function __invoke(SendEmailMessage $message, UserRepository $userRepository)
    {
        $subscribedUsers = $this->userRepository->findBy(['subscription_to_newsletter' => true]);

        $now = new \DateTime();
        $endDate = (new \DateTime())->add(new \DateInterval('P7D'));
        $upcomingGames = $this->videoGameRepository->findByReleaseDate($now, $endDate);

        $this->mailerService->sendEmail(
            $message->getRecipientEmail(), 
            'Notification quotidienne',
            'emails/newsletter.twig',
            ['upcomingGames' => $upcomingGames]
        );
    }
}