<?php

namespace NastyaKuznet\Blog\Factory;

use NastyaKuznet\Blog\Middleware\RoleMiddleware;
use NastyaKuznet\Blog\Service\DatabaseService;
use Psr\Container\ContainerInterface;

class RoleMiddlewareFactory
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function __invoke(ContainerInterface $container): RoleMiddleware
    {
        return new RoleMiddleware($this->allowedRoles, $container->get(DatabaseService::class));
    }
}

