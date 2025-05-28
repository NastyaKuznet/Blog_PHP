<?php

namespace NastyaKuznet\Blog\Controller;

use NastyaKuznet\Blog\Service\interfaces\UserServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Throwable;

class UsersAdminController
{
    private Twig $view;
    private UserServiceInterface $userService;
    

    public function __construct(UserServiceInterface $userService, Twig $view)
    {
        $this->userService = $userService;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        try{
            $allUsers = $this->userService->getAllUsers();
            return $this->view->render($response, 'admin/users.twig', [
                'users' => $allUsers,
            ]);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }

    public function changeRole (Request $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $userId = (int) ($parsedBody['user_id'] ?? 0);
        $newRoleId = (int) ($parsedBody['new_role_id'] ?? 0);
        try {
            $this->userService->changeUserRole($userId, $newRoleId);
            return $response->withHeader('Location', '/admin/users')->withStatus(302);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }

    public function toggleBan(Request $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $userId = (int) ($parsedBody['user_id'] ?? 0);
        $isBanned = (bool)$parsedBody['is_banned'];

        try {
            $this->userService->toggleUserBan($userId, $isBanned);
            return $response->withHeader('Location', '/admin/users')->withStatus(302);
        } catch (Throwable) {
            $response->getBody()->write(json_encode(['error' => 'Internal Server Error']));
            return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json');
        } 
    }
    
}