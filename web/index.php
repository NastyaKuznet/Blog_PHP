<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

// Включаем вывод ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

// Создаем приложение
$app = AppFactory::create();

$app->addBodyParsingMiddleware();

// Настройка Twig
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

// Группа маршрутов API
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

$app->run();