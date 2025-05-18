<?php

namespace NastyaKuznet\Blog\Controller;

use NastyaKuznet\Blog\Service\DatabaseService;
use NastyaKuznet\Blog\Service\UserService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;
use Slim\Views\Twig;
use NastyaKuznet\Blog\Middleware\RoleMiddlewareFactory;

class UsersAdminController
{
    private DatabaseService $databaseService;
    private Twig $view;
    private UserService $userService;
    

    public function __construct(DatabaseService $databaseService, UserService $userService, Twig $view)
    {
        $this->databaseService = $databaseService;
        $this->userService = $userService;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        $allUsers = $this->userService->getAllUsers();
        return $this->view->render($response, 'admin/users.twig', [
            'users' => $allUsers,
        ]);
    }

    public function changeRole (Request $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $userId = (int) ($parsedBody['user_id'] ?? 0);
        $newRoleId = (int) ($parsedBody['new_role_id'] ?? 0);

        $isSuccess = $this->userService->changeUserRole($userId, $newRoleId);
        if($isSuccess)
        {
            return $response->withHeader('Location', '/admin/users')->withStatus(302);
        }

        $response = new SlimResponse();
        $response->getBody()->write('Error in change user`s role.');
        return $response->withStatus(500);
    }

    public function toggleBan(Request $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $userId = (int) ($parsedBody['user_id'] ?? 0);
        $isBanned = (bool) ($parsedBody['is_banned'] ?? false);

        $isSuccess = $this->databaseService->toggleUserBan($userId, $isBanned);

        if ($isSuccess) {
            return $response->withHeader('Location', '/admin/users')->withStatus(302);
        }

        $response = new SlimResponse();
        $response->getBody()->write('Error in toggling ban status.');
        return $response->withStatus(500);
    }
    
}