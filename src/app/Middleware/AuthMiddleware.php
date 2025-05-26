<?php

namespace NastyaKuznet\Blog\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use NastyaKuznet\Blog\Service\AuthService;
use Slim\Psr7\Response\RedirectResponse;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Response as SlimResponse;

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
                $response = new SlimResponse();
                $response->getBody()->write('Authentication required');
                return $response->withStatus(401);
            }
        }

        session_start();
        
        // Продолжаем обработку
        return $handler->handle($request);
    }
}