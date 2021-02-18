<?php


namespace App\Controller;


use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class SecurityController
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * SecurityController constructor.
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @Route ("/login", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils) {
        return new Response($this->twig->render(
            "security/login.html.twig",
            [
                'last_username' => $authenticationUtils->getLastUsername(),
                'error' => $authenticationUtils->getLastAuthenticationError(),
            ]
        ));
    }


    /**
     * @Route ("/logout", name="security_logout")
     */
    public function logout() {

    }

    /**
     * @Route("/comfirm/{token}", name="security_confirm")
     * @param string $token
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function confirm(string $token, UserRepository $userRepository, EntityManagerInterface $entityManager) {
        $user = $userRepository->findOneBy([
            'confirmationToken' => $token
        ]);

        if ($user !== null) {
            $user->setEnabled(true);
            $user->setConfirmationToken('');

            $entityManager->flush();
        }

        return new Response($this->twig->render('security/confirmation.html.twig',
            [
                'user' => $user
            ]
        ));
    }

}