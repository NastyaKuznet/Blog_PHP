<?php
use Slim\Factory\AppFactory;
use NastyaKuznet\Blog\Controller\PostController;
use NastyaKuznet\Blog\Controller\AuthController;
use NastyaKuznet\Blog\Controller\CommentController;
use NastyaKuznet\Blog\Middleware\RoleMiddleware;
use NastyaKuznet\Blog\Middleware\AuthMiddleware;
use NastyaKuznet\Blog\Controller\CategoryController;
use Slim\Views\TwigMiddleware;
use Slim\Routing\RouteCollectorProxy;
use NastyaKuznet\Blog\Controller\UserAccountController;
use NastyaKuznet\Blog\Controller\UsersAdminController;

require __DIR__ . '/../vendor/autoload.php';

// Включаем вывод ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Создаем контейнер
$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../src/app/config/dependencies.php');
$container = $containerBuilder->build();
// 2. Создаем Slim приложение, передавая контейнер
$app = AppFactory::createFromContainer($container);

session_start();

// Add Twig-View Middleware
$app->add(TwigMiddleware::createFromContainer($app));

// Add Routing Middleware
$app->addRoutingMiddleware();

// загуглить что это
$app->addBodyParsingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Dependency Injection Container (DI Container)
$container = $app->getContainer();

$app->add($container->get(RoleMiddleware::class));
$app->add($container->get(AuthMiddleware::class));

$app->get('/login', [AuthController::class, 'login']);
$app->post('/login', [AuthController::class, 'login']);
$app->get('/register', [AuthController::class, 'register']);
$app->post('/register', [AuthController::class, 'register']);

$app->post('/logout', [AuthController::class, 'logout']);

$app->get('/debug/routes', function ($request, $response) use ($app) {
    $routes = $app->getRouteCollector()->getRoutes();
    $routePatterns = array_map(fn($r) => $r->getPattern(), $routes);

    $response->getBody()->write("<pre>" . print_r($routePatterns, true) . "</pre>");
    return $response;
});

//$app->get('/', [AuthController::class, 'home']);

// Группировка роутов по префиксу 'post'
$app->group('/post', function (RouteCollectorProxy $group) use ($container) {
    $group->get('/create', [PostController::class, 'create']);
    $group->post('/create', [PostController::class, 'create']);
    $group->get('/edit/{id}', [PostController::class, 'edit']);
    $group->post('/edit/{id}', [PostController::class, 'edit']);
});

$app->group('/admin', function (RouteCollectorProxy $group) use ($container) {
    $group->get('/users', [UsersAdminController::class, 'index']);
    $group->post('/change_role', [UsersAdminController::class, 'changeRole']);
    $group->post('/toggle_ban', [UsersAdminController::class, 'toggleBan']); 
});

$app->get('/categories', [CategoryController::class, 'index']);
$app->get('/category/create', [CategoryController::class, 'create']);
$app->post('/category/create', [CategoryController::class, 'create']);
$app->post('/category/delete/{id}', [CategoryController::class, 'delete']);

$app->get('/', [PostController::class, 'index']);

$app->get('/post-non-publish', [PostController::class, 'indexNonPublish']);

$app->map(['GET', 'POST'],'/post-non-publish/{id}', [PostController::class, 'editNonPublish']);

$app->map(['GET', 'POST'],'/post/{id}', [PostController::class, 'show']);

$app->post('/post/{id}/like', [PostController::class, 'likePost']);

$app->get('/account', [UserAccountController::class, 'index']);

$app->get('/comment/edit/{id}', [CommentController::class, 'editForm']);
$app->post('/comment/edit/{id}', [CommentController::class, 'update']);
$app->post('/comment/delete/{id}', [CommentController::class, 'delete']);

$app->run(); 
