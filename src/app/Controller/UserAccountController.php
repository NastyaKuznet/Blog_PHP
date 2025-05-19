<?php

namespace NastyaKuznet\Blog\Controller;

use NastyaKuznet\Blog\Service\PostService;
use NastyaKuznet\Blog\Service\UserService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class UserAccountController
{
    private PostService $postService;
    private UserService $userService;
    private Twig $view;

    public function __construct(PostService $postService, UserService $userService, Twig $view)
    {
        $this->postService = $postService;
        $this->userService = $userService;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        $userFromAttribute = $request->getAttribute('user');
        $userId = $userFromAttribute['id'];
        $user = $this->userService->getUser($userId);
        $countPosts = $this->postService->getCountPosts($userId);
        $posts = $this->postService->getPostsByUserId($userId);
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