<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class BlogController
{
    private $twig;

    private $session;
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * BlogController constructor.
     * @param Environment $twig
     * @param RouterInterface $router
     * @param SessionInterface $session
     */
    public function __construct(Environment $twig, RouterInterface $router, SessionInterface $session)
    {
        $this->twig = $twig;
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * @Route("/blog", name="blog_index")
     */
    public function index(): Response
    {
        $html = $this->twig->render("blog/index.html.twig", [
            'posts' => $this->session->get("posts")
        ]);
        return new Response($html);
    }

    /**
     * @Route("/blog/add",  name="blog_add")
     */
    public function add(): RedirectResponse
    {
        $posts = $this->session->get("posts");
        $posts[uniqid()] = [
            'title' => 'A random title '.rand(1,500),
            'text' => 'Some random number '.rand(1,500),
            'date' => new \DateTime(),
        ];
        $this->session->set('posts', $posts);

        return new RedirectResponse($this->router->generate("blog_index"));
    }

    /**
     * @Route("/show/{id}", name="blog_show")
     */
    public function show($id): Response
    {
        $posts = $this->session->get('posts');
        if (!$posts || !isset($posts[$id])) {
            echo $posts;
            throw new NotFoundHttpException('Post not found');
        }

        $html = $this->twig->render(
            "blog/post.html.twig",
            [
                'id' => $id,
                'post' => $posts[$id]
            ]
        );

        return new Response($html);
    }

}