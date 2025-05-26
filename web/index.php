<?php
use Slim\Factory\AppFactory;
use NastyaKuznet\Blog\Controller\PostController;
use NastyaKuznet\Blog\Controller\AuthController;
use NastyaKuznet\Blog\Controller\CommentController;
use NastyaKuznet\Blog\Middleware\AuthMiddleware;
use NastyaKuznet\Blog\Middleware\RoleMiddlewareFactory;
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

// Add Twig-View Middleware
$app->add(TwigMiddleware::createFromContainer($app));

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Body Pasrsing MIddleware
$app->addBodyParsingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Dependency Injection Container (DI Container)
$container = $app->getContainer();

$app->get('/', [PostController::class, 'index'])->add($container->get(AuthMiddleware::class));
$app->get('/login', [AuthController::class, 'login']);
$app->post('/login', [AuthController::class, 'login']);
$app->get('/register', [AuthController::class, 'register']);
$app->post('/register', [AuthController::class, 'register']);
$app->post('/logout', [AuthController::class, 'logout'])->add($container->get(AuthMiddleware::class));
$app->get('/account', [UserAccountController::class, 'index'])->add($container->get(AuthMiddleware::class));
$app->get('/debug/routes', function ($request, $response) use ($app) {
    $routes = $app->getRouteCollector()->getRoutes();
    $routePatterns = array_map(fn($r) => $r->getPattern(), $routes);

    $response->getBody()->write("<pre>" . print_r($routePatterns, true) . "</pre>");
    return $response;
});
$app->group('/post', function (RouteCollectorProxy $group) use ($container) {
    $group->get('/create', [PostController::class, 'create']);
    $group->post('/create', [PostController::class, 'create']);
    $group->map(['GET', 'POST'],'/{id}', [PostController::class, 'show']);
    $group->post('/{id}/like', [PostController::class, 'likePost']);
    $group->get('/edit/{id}', [PostController::class, 'edit']);
    $group->post('/edit/{id}', [PostController::class, 'edit']);
})->add(function ($request, $handler) use ($container) {
        $roleMiddlewareFactory = $container->get(RoleMiddlewareFactory::class);
        $uri = $request->getUri()->getPath();
        $allowedRoles = [];
        if ($uri === '/post/create') {
            $allowedRoles = ['writer', 'moderator', 'admin'];
        } elseif (strpos($uri, '/post/edit/') === 0) {
            $allowedRoles = ['moderator', 'admin'];
        } else {
            $allowedRoles = ['writer', 'moderator', 'admin'];
        }
        $editorRoleMiddleware = $roleMiddlewareFactory($container, $allowedRoles);
        return $editorRoleMiddleware($request, $handler);
})->add($container->get(AuthMiddleware::class));
$app->group('/admin', function (RouteCollectorProxy $group) use ($container) {
    $group->get('/users', [UsersAdminController::class, 'index']);
    $group->post('/change_role', [UsersAdminController::class, 'changeRole']);
    $group->post('/toggle_ban', [UsersAdminController::class, 'toggleBan']); 
})->add(function ($request, $handler) use ($container) {
        $roleMiddlewareFactory = $container->get(RoleMiddlewareFactory::class);
        $adminRoleMiddleware = $roleMiddlewareFactory($container, ['admin']);
        return $adminRoleMiddleware($request, $handler);
})->add($container->get(AuthMiddleware::class));
$app->get('/categories', [CategoryController::class, 'index']
)->add(function ($request, $handler) use ($container) {
        $roleMiddlewareFactory = $container->get(RoleMiddlewareFactory::class);
        $adminRoleMiddleware = $roleMiddlewareFactory($container, ['moderator', 'admin']);
        return $adminRoleMiddleware($request, $handler);
})->add($container->get(AuthMiddleware::class));
$app->group('/category', function (RouteCollectorProxy $group) use ($container) {
    $group->get('/create', [CategoryController::class, 'create']);
    $group->post('/create', [CategoryController::class, 'create']);
    $group->post('/delete/{id}', [CategoryController::class, 'delete']);
})->add(function ($request, $handler) use ($container) {
        $roleMiddlewareFactory = $container->get(RoleMiddlewareFactory::class);
        $adminRoleMiddleware = $roleMiddlewareFactory($container, ['moderator', 'admin']);
        return $adminRoleMiddleware($request, $handler);
})->add($container->get(AuthMiddleware::class));
$app->group('/post-non-publish', function (RouteCollectorProxy $group) use ($container) {
    $group->get('', [PostController::class, 'indexNonPublish']);
    $group->map(['GET', 'POST'],'/{id}', [PostController::class, 'editNonPublish']);
})->add(function ($request, $handler) use ($container) {
        $roleMiddlewareFactory = $container->get(RoleMiddlewareFactory::class);
        $adminRoleMiddleware = $roleMiddlewareFactory($container, ['moderator', 'admin']);
        return $adminRoleMiddleware($request, $handler);
})->add($container->get(AuthMiddleware::class));
$app->group('/comment', function (RouteCollectorProxy $group) use ($container) {
    $group->get('/edit/{id}', [CommentController::class, 'editForm']);
    $group->post('/edit/{id}', [CommentController::class, 'update']);
    $group->post('/delete/{id}', [CommentController::class, 'delete']);
})->add(function ($request, $handler) use ($container) {
        $roleMiddlewareFactory = $container->get(RoleMiddlewareFactory::class);
        $adminRoleMiddleware = $roleMiddlewareFactory($container, ['writer', 'moderator', 'admin']);
        return $adminRoleMiddleware($request, $handler);
})->add($container->get(AuthMiddleware::class));

$app->run(); 
