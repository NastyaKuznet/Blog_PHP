<?php

namespace NastyaKuznet\Blog\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use NastyaKuznet\Blog\Service\AuthService;

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

        if ($token) {
            $payload = $this->authService->decodeJwtToken($token, $this->secretKey);

            if ($payload !== null && isset($payload['exp']) && $payload['exp'] >= time()) {
                // Токен валиден — добавляем пользователя в запрос
                $request = $request->withAttribute('user', $payload);
            } else {
                setcookie(
                    'token',
                    '',
                    [
                        'expires' => time() - 3600,
                        'path' => '/',
                        'httponly' => true,
                        'secure' => true,
                        'samesite' => 'Strict',
                    ]
                );
            }
        }

        session_start();
        
        // Продолжаем обработку
        return $handler->handle($request);
    }
}