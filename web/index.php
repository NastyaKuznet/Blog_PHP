<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Slim\Factory\AppFactory;
use NastyaKuznet\Blog\Controller\PostController;
use NastyaKuznet\Blog\Middleware\RoleMiddleware;
use Slim\Routing\RouteCollectorProxy;

$app = AppFactory::create();

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Dependency Injection Container (DI Container)
$container = $app->getContainer();

// Группировка роутов по префиксу 'post'
$app->group('/post', function (RouteCollectorProxy $group) {
    // Роуты, требующие роль 'writer' или выше
    $group->get('/create', [PostController::class, 'create'])->add(new RoleMiddleware(['writer', 'moder', 'admin']));
    $group->post('/create', [PostController::class, 'create'])->add(new RoleMiddleware(['writer', 'moder', 'admin']));
    // Роуты, требующие роль 'moder' или выше
    $group->get('/edit/{id}', [PostController::class, 'edit'])->add(new RoleMiddleware(['moder', 'admin']));
    $group->post('/edit/{id}', [PostController::class, 'edit'])->add(new RoleMiddleware(['moder', 'admin']));
});

$app->get('/', [PostController::class, 'index']);

$app->map(['GET', 'POST'],'/post/{id}', [PostController::class, 'show']);

$app->post('/post/{id}/like', [PostController::class, 'likePost']);

//Заглушки для admins
$app->get('/users', [PostController::class, 'users'])->add(new RoleMiddleware(['admin']));


$app->run(); 