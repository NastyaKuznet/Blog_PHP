<?php

namespace NastyaKuznet\Blog\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use NastyaKuznet\Blog\Service\AuthService;
use Slim\Psr7\Response\RedirectResponse;
use Slim\Psr7\Factory\ResponseFactory;

class AuthMiddleware
{
    private $authService;
    private $secretKey;

    public function __construct(AuthService $authService, string $secretKey)
    {
        $this->authService = $authService;
        $this->secretKey = $secretKey;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Разрешённые маршруты без авторизации
        $allowedRoutes = ['/login', '/register', '/api/login', '/api/register'];

        // Получаем текущий URI
        $uri = $request->getUri()->getPath();

        // Пропускаем, если маршрут разрешён
        if (in_array($uri, $allowedRoutes)) {
            return $handler->handle($request);
        }

        // Получаем токен из кук
        $token = $request->getCookieParams()['token'] ?? null;

        if (!$token) {
            return (new ResponseFactory())->createResponse(401)
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        $payload = $this->authService->decodeJwtToken($token, $this->secretKey);

        if ($payload === null || !isset($payload['exp']) || $payload['exp'] < time()) {
            return (new ResponseFactory())->createResponse(401)
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        // Добавляем пользователя в атрибуты запроса
        $request = $request->withAttribute('user', $payload);

        // Продолжаем обработку
        return $handler->handle($request);
    }
}