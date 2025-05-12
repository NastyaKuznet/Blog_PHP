<?php

use NastyaKuznet\Blog\Controller\PostController;
use NastyaKuznet\Blog\Controller\AuthController;
use NastyaKuznet\Blog\Controller\UserAccountController;
use NastyaKuznet\Blog\Controller\UsersAdminController;
use NastyaKuznet\Blog\Middleware\RoleMiddleware;
use NastyaKuznet\Blog\Service\DatabaseService;
use NastyaKuznet\Blog\Service\PostService;
use NastyaKuznet\Blog\Service\AuthService;
use NastyaKuznet\Blog\Service\UserService;
use Twig\Loader\FilesystemLoader;
use Slim\Views\Twig;
use function DI\create;
use function DI\get;

return [
    'config' => require __DIR__ . '/config.php',

    'view' => function () {
        $loader = new FilesystemLoader(__DIR__ . '/../templates');
        return new Twig($loader, ['cache' => false]);
    },

    DatabaseService::class => create(DatabaseService::class)
        ->constructor(get('config')),

    PostService::class => create(PostService::class)
        ->constructor(get(DatabaseService::class)),

    PostController::class => create(PostController::class)
        ->constructor(get(PostService::class), get('view')),

    AuthController::class => create(AuthController::class)
        ->constructor(get(AuthService::class), get('view')),

    UserAccountController::class => create(UserAccountController::class)
        ->constructor(get(PostService::class), get(UserService::class), get('view')),
    
    UsersAdminController::class => create(UsersAdminController::class)
        ->constructor(get(DatabaseService::class), get('view')),

    AuthService::class => create(AuthService::class)
        ->constructor(get(DatabaseService::class)),
    
    UserService::class => create(UserService::class)
        ->constructor(get(DatabaseService::class)),
];