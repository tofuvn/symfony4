<?php


namespace App\Controller;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FollowingController
 * @package App\Controller
 * @Security("is_granted('ROLE_USER')")
 * @Route("/following")
 */
class FollowingController extends AbstractController
{
    /**
     * @Route ("/follow/{id}", name="following_follow")
     * @param User $userToFollow
     * @return RedirectResponse
     */
    public function follow(User $userToFollow)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $currentUser->follow($userToFollow);

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('micro_post_user', ['username' => $userToFollow->getUsername()]);
    }

    /**
     * @Route ("/unfollow/{id}", name="following_unfollow")
     */
    public function unfollow(User $userToUnfollow): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $currentUser->getFollowing()->removeElement($userToUnfollow);

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('micro_post_user', ['username' => $userToUnfollow->getUsername()]);

    }
}