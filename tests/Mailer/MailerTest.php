<?php

namespace App\Tests\Mailer;

use App\Entity\User;
use App\Mailer\Mailer;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class MailerTest extends TestCase
{
    public function testConfirmationEmail()
    {
        $user = new User();
        $user->setEmail('theho@gmail.com');

        $swiftMailer = $this->getMockBuilder(\Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twigMock = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();



        $swiftMailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($subject) {
                $message = (string) $subject;
                dump($message);
                return strpos($message, 'From: pl@gmail.com')  !== false;
            }));

        $twigMock->expects($this->once())->method('render')
            ->with('email/registration.html.twig', [
                'user' => $user
            ])
            //->willReturn('This is a message body')
        ;

        $mailer = new Mailer($swiftMailer, $twigMock, 'pl@gmail.com');
        $mailer->sendConfirmationEmail($user);
    }
}
