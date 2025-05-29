<?php

namespace NastyaKuznet\Blog\Middleware;

class RoleMiddlewareFactory
{
    public function __invoke(array $allowedRoles): RoleMiddleware
    {
        return new RoleMiddleware($allowedRoles);
    }
}