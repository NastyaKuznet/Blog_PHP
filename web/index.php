<?php
use Slim\Factory\AppFactory;
use NastyaKuznet\Blog\Controller\PostController;
use NastyaKuznet\Blog\Middleware\RoleMiddleware;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\Routing\RouteCollectorProxy;

// 1. Создаем контейнер
$containerBuilder = new \DI\ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../src/app/config/dependencies.php');
$container = $containerBuilder->build();
// 2. Создаем Slim приложение, передавая контейнер
$app = AppFactory::createFromContainer($container);

// Add Twig-View Middleware
$app->add(TwigMiddleware::createFromContainer($app));

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
