<?php


namespace App\Controller;


use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NotificationController
 * @package App\Controller
 * @Security("is_granted('ROLE_USER')")
 * @Route ("/notification")
 */
class NotificationController extends AbstractController
{
    /**
     * @var NotificationRepository
     */
    private $notificationRepository;

    /**
     * NotificationController constructor.
     * @param NotificationRepository $notificationRepository
     */
    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @Route ("/unread-count", name="notification_unread")
     */
    public function unreadCount(): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        return new JsonResponse([
            'count' => $this->notificationRepository->findUnseenByUser($currentUser)
        ]);
    }

    /**
     * @Route("/all", name="notification_all")
     */
    public function notifications(): Response
    {
        return $this->render(
            'notification/notifications.html.twig',
            ['notifications' => $this->notificationRepository->findBy([
                'seen' => false,
                'user' => $this->getUser()
            ])]
        );
    }

    /**
     * @param Notification $notification
     * @return RedirectResponse
     * @Route("/acknowledge/{id}", name="notification_acknowledge")
     */
    public function acknowledge(Notification $notification): RedirectResponse
    {
        $notification->setSeen(true);

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('notification_all');
    }

    /**
     * @return RedirectResponse
     * @Route("/acknowledge-all", name="notification_acknowledge_all")
     */
    public function acknowledgeAll(): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $this->notificationRepository->markAllAsReadByUser($currentUser);

        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('notification_all');


    }

}