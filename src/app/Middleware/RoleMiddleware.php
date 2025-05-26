<?php

namespace NastyaKuznet\Blog\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class RoleMiddleware
{
    private array $routePermissions;

    public function __construct(array $routePermissions)
    {
        $this->routePermissions = $routePermissions;
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        // Получаем текущий URI
        $uri = $request->getUri()->getPath();

        // Получаем пользователя из атрибутов запроса
        $user = $request->getAttribute('user');

        // Определяем роль: если пользователь не авторизован - он читатель
        $role = is_array($user) && !empty($user['role']) ? $user['role'] : 'reader'; 

        // Получаем разрешенные маршруты для роли
        $allowedRoutes = $this->getAllowedRoutesForRole($role);

        if ($this->isRouteAllowed($uri, $allowedRoutes)) {
            return $handler->handle($request);
        }

        return $this->denyAccess();
    }

    private function getAllowedRoutesForRole(string $role): array
    {
        if (!isset($this->routePermissions['roles'][$role])) {
            return [];
        }

        $roleConfig = $this->routePermissions['roles'][$role];
        $routes = $roleConfig['routes'] ?? [];

        // Рекурсивно добавляем унаследованные маршруты
        if (isset($roleConfig['extends'])) {
            $inherited = $this->getAllowedRoutesForRole($roleConfig['extends']);
            $routes = array_merge($inherited, $routes);
        }

        return $routes;
    }

    private function isRouteAllowed(string $uri, array $allowedRoutes): bool
    {
        foreach ($allowedRoutes as $route) {
            if (str_starts_with($route, '#')) {
                if (preg_match($route, $uri)) {
                    return true;
                }
            } elseif ($uri === $route) {
                return true;
            }
        }
        return false;
    }

    private function denyAccess(): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write('Access denied');
        return $response->withStatus(403);
    }
}
