<?php
use Slim\Factory\AppFactory;
use NastyaKuznet\Blog\Controller\PostController;
use NastyaKuznet\Blog\Middleware\RoleMiddleware;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\Routing\RouteCollectorProxy;
use DI\ContainerBuilder;

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

// загуглить что это
$app->addBodyParsingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Dependency Injection Container (DI Container)
$container = $app->getContainer();

$app->group('/api', function ($group) {
    $group->post('/register', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'user';

        if (empty($username) || empty($password)) {
            $html = '<div class="error">Заполните имя и пароль</div>';
            $response->getBody()->write($html); // <- Добавьте эту строку
            return $response
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(400);
        }

        // Здесь будет логика сохранения пользователя

        $html = '<div class="success">Регистрация успешна!</div>';
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    });

    // Вход
    $group->post('/login', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            $html = '<div class="error">Пупуп</div>';
            $response->getBody()->write($html);
            return $response
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(400);
        }

        // Здесь будет логика аутентификации

        $html = '<div class="success">Вход выполнен! Токен: fake-token</div>';
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    });
});

$app->get('/', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'index.twig');
});

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
