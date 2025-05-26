<?php

namespace NastyaKuznet\Blog\Middleware;

use Psr\Container\ContainerInterface;

class RoleMiddlewareFactory
{
    public function __invoke(ContainerInterface $container, array $allowedRoles): RoleMiddleware
    {
        return new RoleMiddleware($allowedRoles);
    }
}