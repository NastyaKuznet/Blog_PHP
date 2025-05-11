<?php

namespace NastyaKuznet\Blog\Middleware;

use NastyaKuznet\Blog\Service\DatabaseService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class RoleMiddleware
{
    private array $allowedRoles;
    private DatabaseService $databaseService;

    public function __construct(array $allowedRoles, DatabaseService $databaseService)
    {
        $this->allowedRoles = $allowedRoles;
        $this->databaseService = $databaseService;
    }

    public function __invoke(Request $request, Handler $handler): Response
    {
        $userRoleId = 3;

        $userRoleName = $this->databaseService->getNameRoleById($userRoleId);

        if (!in_array($userRoleName, $this->allowedRoles)) {
            $response = new SlimResponse();
            $response->getBody()->write('Access denied.  Требуется роль: ' . implode(', ', $this->allowedRoles));
            return $response->withStatus(403);
        }

        $response = $handler->handle($request);
        return $response;
    }
}
