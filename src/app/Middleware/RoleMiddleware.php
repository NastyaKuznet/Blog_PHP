<?php

namespace NastyaKuznet\Blog\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class RoleMiddleware
{

    public function __invoke(Request $request, Handler $handler): Response
    {
        // Разрешённые маршруты без авторизации
        $allowedRoutes = [
            '/', 
            '/login', 
            '/register', 
            '/logout',
            '/post', 
            '#^/post/\d+$#', 
            '#^/post/\d+/like$#'
        ];

        // Получаем текущий URI
        $uri = $request->getUri()->getPath();

        foreach ($allowedRoutes as $route) {            
            if (str_starts_with($route, '#')) {
                $matchResult = preg_match($route, $uri);
                
                if ($matchResult) {
                    return $handler->handle($request);
                }
            } else {
                if ($uri === $route) {
                    return $handler->handle($request);
                }
            }
        }

        // Получаем пользователя из атрибутов запроса
        $user = $request->getAttribute('user');
        //die($user);

        // Проверяем, установлен ли атрибут user и является ли он массивом
        if (!is_array($user) || $user === null) {
            $response = new SlimResponse();
            $response->getBody()->write('Access denied');
            return $response->withStatus(403);
        }

        $routes = [
            'reader'    => ['/post', '#^/post/\d+$#', '#^/post/\d+/like$#'],
            'writer'    => ['/post', '#^/post/\d+$#', '#^/post/\d+/like$#', '/account', '/post/create'],
            'moderator' => ['/post', '#^/post/\d+$#', '#^/post/\d+/like$#', '/account', '/post/create', '#^/post/edit/\d+$#'],
            'admin'     => ['/post', '#^/post/\d+$#', '#^/post/\d+/like$#', '/account', '/post/create', '#^/post/edit/\d+$#', '/admin/users', '/admin/change_role', '/admin/delete_user'],
        ];

        $role = $user['role'];

        if (isset($routes[$role])) {
            foreach ($routes[$role] as $route) {
                if (strpos($route, '#') === 0) {  // Если начинается с #, значит это регулярное выражение
                    if (preg_match($route, $uri)) {
                        return $handler->handle($request);
                    }
                } else { // Иначе - простое сравнение строк
                    if ($uri === $route) {
                        return $handler->handle($request);
                    }
                }
            }
        }

        $response = new SlimResponse();
        $response->getBody()->write('Access denied');
        return $response->withStatus(403);
    }

    private function isRouteAllowed(string $uri, array $allowedRoutes): bool
    {
        foreach ($allowedRoutes as $route) {
            echo($route);
            if (preg_match($route, $uri)) {
                return true;
            }
        }
        return false;
    }
}
