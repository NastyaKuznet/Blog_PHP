<?php

namespace NastyaKuznet\Blog\Controller;

use NastyaKuznet\Blog\Service\interfaces\PostServiceInterface;
use NastyaKuznet\Blog\Service\interfaces\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class UserAccountController
{
    private PostServiceInterface $postService;
    private UserServiceInterface $userService;
    private Twig $view;

    public function __construct(PostServiceInterface $postService, UserServiceInterface $userService, Twig $view)
    {
        $this->postService = $postService;
        $this->userService = $userService;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        $userFromAttribute = $request->getAttribute('user');
        $userId = $userFromAttribute['id'];
        $user = $this->userService->get($userId);
        $countPosts = $this->postService->getCountPostsByUserId($userId);
        $posts = $this->postService->getByUserId($userId);
        return $this->view->render($response, 'user/userAccount.twig', [
            'posts' => $posts,
            'user' => $user,
            'countPosts' => $countPosts,
            'app' => [
                'request' => $request,
            ],
        ]);
    }
}