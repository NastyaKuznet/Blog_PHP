<?php

use NastyaKuznet\Blog\Controller\AuthController;
use NastyaKuznet\Blog\Controller\PostController;
use NastyaKuznet\Blog\Controller\CommentController;
use NastyaKuznet\Blog\Controller\UserAccountController;
use NastyaKuznet\Blog\Controller\UsersAdminController;
use NastyaKuznet\Blog\Controller\CategoryController;
use NastyaKuznet\Blog\Middleware\AuthMiddleware;
use NastyaKuznet\Blog\Middleware\RoleMiddleware;
use NastyaKuznet\Blog\Middleware\RoleMiddlewareFactory;
use NastyaKuznet\Blog\Service\AuthService;
use NastyaKuznet\Blog\Service\CommentService;
use NastyaKuznet\Blog\Service\DatabaseService;
use NastyaKuznet\Blog\Service\PostService;
use NastyaKuznet\Blog\Service\UserService;
use NastyaKuznet\Blog\Service\CategoryService;
use Twig\Loader\FilesystemLoader;
use Slim\Views\Twig;
use function DI\create;
use function DI\get;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../..');
$dotenv->load();

return [
    'config' => require __DIR__ . '/config.php',

    'view' => function () {
        $loader = new FilesystemLoader(__DIR__ . '/../templates');
        return new Twig($loader, ['cache' => false]);
    },

    // Секретный ключ из .env
    'config.jwt_secret' => $_ENV['JWT_SECRET'],
    
    AuthService::class => create(AuthService::class)
        ->constructor(get(DatabaseService::class)),

    CategoryService::class => create(CategoryService::class)
        ->constructor(get(DatabaseService::class)),

    CommentService::class => create(CommentService::class)
        ->constructor(get(DatabaseService::class)),

    DatabaseService::class => create(DatabaseService::class)
        ->constructor(get('config')),

    PostService::class => create(PostService::class)
        ->constructor(get(DatabaseService::class)),
    
    UserService::class => create(UserService::class)
        ->constructor(get(DatabaseService::class)),
    
    AuthMiddleware::class => create(AuthMiddleware::class)
        ->constructor(get(AuthService::class), get('config.jwt_secret')),

    RoleMiddlewareFactory::class => create(RoleMiddlewareFactory::class)
        ->constructor(),

    AuthController::class => create(AuthController::class)
        ->constructor(get(AuthService::class), get('view')),
    
    CategoryController::class => create(CategoryController::class)
        ->constructor(get(CategoryService::class), get('view')),

    CommentController::class => create(CommentController::class)
        ->constructor(get(CommentService::class), get('view')),

    PostController::class => create(PostController::class)
        ->constructor(get(PostService::class), get(CategoryService::class), get(CommentService::class), get('view')),

    UserAccountController::class => create(UserAccountController::class)
        ->constructor(get(PostService::class), get(UserService::class), get('view')),
    
    UsersAdminController::class => create(UsersAdminController::class)
        ->constructor(get(UserService::class), get('view')),
];
