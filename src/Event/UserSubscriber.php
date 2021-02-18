<?php


namespace App\Event;


use App\Entity\UserPreferences;
use App\Mailer\Mailer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    /**
     * @var Mailer
     */
    private $mailer;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var string
     */
    private $defaultLanguage;


    /**
     * UserSubscriber constructor.
     * @param Mailer $mailer
     * @param EntityManagerInterface $entityManager
     * @param string $defaultLocale
     */
    public function __construct(Mailer $mailer, EntityManagerInterface $entityManager, string $defaultLocale)
    {

        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->defaultLanguage = $defaultLocale;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisterEvent::NAME => 'onUserRegister'
        ];
    }

    public function onUserRegister(UserRegisterEvent $event)
    {
        $preferences = new UserPreferences();
        $preferences->setLocale($this->defaultLanguage);

        $user = $event->getRegisterUser();
        $user->setPreferences($preferences);

        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException | ORMException $e) {
        }

        $this->mailer->sendConfirmationEmail($event->getRegisterUser());
    }

}