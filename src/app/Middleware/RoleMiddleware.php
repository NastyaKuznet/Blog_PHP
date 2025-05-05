<?php

namespace NastyaKuznet\Blog\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class RoleMiddleware
{
    private array $allowedRoles;
    private array $config;


    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
        $this->config = include __DIR__ . '/../config/config.php';
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        // Временная заглушка: определяем роль пользователя "статически"
        $userRole = 'reader'; // Замените это реальной логикой аутентификации и авторизации

        // Находим роль пользователя в конфиге
        foreach ($this->config['users'] as $user) {
            if ($user['nickname'] === 'Moder1') { // Опять же, это заглушка!
                $userRoleId = $user['roleId'];
                break;
            }
        }

        // Получаем имя роли по ID
        $userRoleName = '';
        foreach ($this->config['roles'] as $role) {
            if ($role['id'] === $userRoleId) {
                $userRoleName = $role['name'];
                break;
            }
        }

        if (!in_array($userRoleName, $this->allowedRoles)) {
            $response = new SlimResponse();
            $response->getBody()->write('Access denied.  Требуется роль: ' . implode(', ', $this->allowedRoles));
            return $response->withStatus(403);
        }

        $response = $handler->handle($request);
        return $response;
    }
}
