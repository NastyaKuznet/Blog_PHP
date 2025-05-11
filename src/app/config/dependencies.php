<?php

use NastyaKuznet\Blog\Controller\PostController;
use NastyaKuznet\Blog\Middleware\RoleMiddleware;
use NastyaKuznet\Blog\Service\DatabaseService;
use NastyaKuznet\Blog\Service\PostService;
use Twig\Loader\FilesystemLoader;
use Slim\Views\Twig;
use function DI\create;
use function DI\get;

return [
    'config' => require __DIR__ . '/config.php',

    'view' => create(Twig::class)
        ->constructor(
            create(FilesystemLoader::class)
                ->constructor(__DIR__ . '/../templates'),
            [
                'cache' => false // '/path/to/cache'
            ]
        ),

    DatabaseService::class => create(DatabaseService::class)
        ->constructor(get('config')),

    PostService::class => create(PostService::class)
        ->constructor(get(DatabaseService::class)),

    PostController::class => create(PostController::class)
        ->constructor(get(PostService::class), get('view')),
];