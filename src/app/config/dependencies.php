<?php

use Slim\Views\Twig;
use function DI\create;
use NastyaKuznet\Blog\Service\PostService;
use function DI\get;
use NastyaKuznet\Blog\Controller\PostController;
use Twig\Loader\FilesystemLoader;

return [
    'config' => require __DIR__ . '/../config.php',

    // Twig
    'view' => create(Twig::class)
        ->constructor(
            create(FilesystemLoader::class) // Создаем FilesystemLoader
                ->constructor(__DIR__ . '/../templates'), // Передаем путь к шаблонам
            [
                'cache' => false // '/path/to/cache'
            ]
        ),

    // PostService
    PostService::class => create(PostService::class)
        ->constructor(get('config')),

    // PostController
    PostController::class => create(PostController::class)
        ->constructor(get(PostService::class), get('view')), // Внедряем Twig
];