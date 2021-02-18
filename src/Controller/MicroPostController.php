<?php


namespace App\Controller;

use App\Entity\MicroPost;
use App\Entity\User;
use App\Form\MicroPostType;
use App\Repository\MicroPostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class MicroPostController
 * @package App\Controller
 * @Route ("/micro-post")
 */
class MicroPostController extends AbstractController
{
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var MicroPostRepository
     */
    private $microPostRepository;
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var FlashBagInterface
     */
    private $flashBag;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;
    /**
     * @var UserRepository
     */
    private $userRepository;


    public function __construct(Environment $twig,
                                MicroPostRepository $microPostRepository,
                                FormFactoryInterface $formFactory,
                                EntityManagerInterface $entityManager,
                                RouterInterface $router,
                                FlashBagInterface $flashBag,
                                AuthorizationCheckerInterface $authorizationChecker
                                )
    {
        $this->twig = $twig;
        $this->microPostRepository = $microPostRepository;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->authorizationChecker = $authorizationChecker;
    }


    /**
     * @Route ("/", name="micro_post_index")
     * @param TokenStorageInterface $tokenStorage
     * @param UserRepository $userRepository
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function index(TokenStorageInterface $tokenStorage, UserRepository $userRepository): Response
    {
        $currentUser = $tokenStorage
            ->getToken()
            ->getUser();

        $usersToFollow = [];

        if ($currentUser instanceof User) {
            $posts = $this->microPostRepository->findAllByUsers($currentUser->getFollowing());

            $usersToFollow = count($posts) === 0 ? $userRepository->findAllWithMoreThan5PostExceptUser($currentUser) : [];
        } else {
            $posts = $this->microPostRepository->findBy(
                [],
                ['time' => 'DESC']
            );
        }
        $html = $this->twig->render('micro-post/index.html.twig', [
            'posts' => $posts,
            'usersToFollow' => $usersToFollow,
        ]);
        return new Response($html);
    }

    /**
     * @Route ("/edit/{id}", name="micro_post_edit")
     * @param MicroPost $post
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function edit(MicroPost $post, Request $request)
    {
        $this->denyAccessUnlessGranted("edit", $post);

        $form = $this->formFactory->create(MicroPostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($post);
            $this->entityManager->flush();

            return new RedirectResponse($this->router->generate('micro_post_index'));
        }

        return new Response(
            $this->twig->render(
                'micro-post/add.html.twig', [
                'form' => $form->createView()
            ])
        );
    }

    /**
     * @Route ("/delete/{id}", name="micro_post_delete")
     * @param MicroPost $post
     * @return RedirectResponse
     */
    public function delete(MicroPost $post): RedirectResponse
    {
        $this->denyAccessUnlessGranted("delete", $post);

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        $this->flashBag->add('notice', 'Micro Post was deleted');

        return new RedirectResponse(
            $this->router->generate("micro_post_index")
        );
    }


    /**
     * @Route ("/add", name="micro_post_add")
     * @param Request $request
     * @return Response
     * @Security ("is_granted('ROLE_USER')")
     */
    public function add(Request $request, TokenStorageInterface $tokenStorage): Response
    {
        $user = $tokenStorage->getToken()->getUser();
        $microPost = new MicroPost();
        //$microPost->setTime(new \DateTime());
        $microPost->setUser($user);

        $form = $this->formFactory->create(MicroPostType::class, $microPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($microPost);
            $this->entityManager->flush();

            return new RedirectResponse($this->router->generate('micro_post_index'));
        }

        return new Response(
            $this->twig->render(
                'micro-post/add.html.twig', [
                'form' => $form->createView()
            ])
        );
    }

    /**
     * @Route("/user/{username}", name="micro_post_user")
     */
    public function userPosts(User $userWithPosts): Response
    {
        $html = $this->twig->render('micro-post/user-posts.html.twig',
            [
                //'posts' => $userWithPosts->getPosts(),
                'posts' => $this->microPostRepository->findBy(
                    ['user' => $userWithPosts],
                    ['time' => 'DESC']
                ),
                'user' => $userWithPosts
            ]);
        return new Response($html);
    }

    /**
     * @Route ("/{id}", name="micro_post_post")
     * @param MicroPost $post
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function post(MicroPost $post): Response
    {

        return new Response(
            $this->twig->render(
                'micro-post/post.html.twig',
                [
                    'post' => $post
                ]
            )
        );
    }


}