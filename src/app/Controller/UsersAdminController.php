<?php

namespace NastyaKuznet\Blog\Controller;

use NastyaKuznet\Blog\Service\DatabaseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;
use Slim\Views\Twig;
use NastyaKuznet\Blog\Middleware\RoleMiddlewareFactory;

class UsersAdminController
{
    private DatabaseService $databaseService;
    private Twig $view;

    public function __construct(DatabaseService $databaseService, Twig $view)
    {
        $this->databaseService = $databaseService;
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        $allUsers = $this->databaseService->getAllUsers();
        return $this->view->render($response, 'admin/users.twig', [
            'users' => $allUsers,
        ]);
    }

    public function changeRole (Request $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $userId = (int) ($parsedBody['user_id'] ?? 0);
        $newRoleId = (int) ($parsedBody['new_role_id'] ?? 0);

        try {
            $this->databaseService->changeUserRole($userId, $newRoleId);
            return $response->withHeader('Location', '/admin/users')->withStatus(302);
        }
        catch (\Exception $e) {
            error_log("Ошибка при изменении роли пользователя: " . $e->getMessage());
            return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
        }
    }

    public function deleteUser(Request $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $userId = (int) ($parsedBody['user_id'] ?? 0);

        try {
            $this->databaseService->deleteUser($userId);
            return $response->withHeader('Location', '/admin/users')->withStatus(302);
        } catch (\Exception $e) {
            error_log("Ошибка при удалении пользователя: " . $e->getMessage());
            return $response->withStatus(500)->withHeader('Content-Type', 'text/plain');
        }
    }

}