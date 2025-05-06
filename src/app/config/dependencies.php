<?php

use NastyaKuznet\Blog\Controller\PostController;
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

    PostService::class => create(PostService::class)
        ->constructor(get('config'), get(DatabaseService::class)),

    DatabaseService::class => create(DatabaseService::class)
        ->constructor(get('config')),

    PostController::class => create(PostController::class)
        ->constructor(get(PostService::class), get('view')),
];