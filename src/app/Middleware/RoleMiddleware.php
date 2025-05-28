<?php

namespace NastyaKuznet\Blog\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $user = $request->getAttribute('user');

        if (!isset($user['role']) || !in_array($user['role'], $this->allowedRoles)) {
            $response = new SlimResponse();
            $response->getBody()->write('Insufficient permissions');
            return $response->withStatus(403);
        }

        return $handler->handle($request);
    }
}
