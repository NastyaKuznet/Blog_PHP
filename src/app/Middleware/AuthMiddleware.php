<?php

namespace NastyaKuznet\Blog\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use NastyaKuznet\Blog\Service\AuthService;
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
        // Получаем токен из кук
        $token = $request->getCookieParams()['token'] ?? null;

        if (!$token) {
            return (new ResponseFactory())->createResponse(401)
                ->withHeader('Content-Type', 'text/html')
                ->withBody(\GuzzleHttp\Psr7\Utils::streamFor('<div class="error">Неавторизованный доступ</div>'));
        }

        // Декодируем и проверяем токен
        $payload = $this->authService->decodeJwtToken($token, $this->secretKey);

        if ($payload === null) {
            return (new ResponseFactory())->createResponse(401)
                ->withHeader('Content-Type', 'text/html')
                ->withBody(\GuzzleHttp\Psr7\Utils::streamFor('<div class="error">Неверный или просроченный токен</div>'));
        }

        // Добавляем информацию о пользователе в атрибуты запроса
        $request = $request->withAttribute('user', $payload);

        // Продолжаем обработку
        return $handler->handle($request);
    }
}