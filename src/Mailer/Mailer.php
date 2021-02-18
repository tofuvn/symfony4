<?php


namespace App\Mailer;


use App\Entity\User;
use Twig\Environment;

class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var Environment
     */
    private $environment;
    /**
     * @var string
     */
    private $mailFrom;

    public function __construct(\Swift_Mailer $mailer, Environment $environment, string $mailFrom)
    {
        $this->mailer = $mailer;
        $this->environment = $environment;
        $this->mailFrom = $mailFrom;
    }

    public function sendConfirmationEmail(User $user) {
        $body = $this->environment->render('email/registration.html.twig', [
            'user' => $user
        ]);
        $message = (new \Swift_Message())
            ->setSubject('Welcome to MircoPostApp')
            ->setFrom($this->mailFrom)
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }


}