<?php
use Slim\Factory\AppFactory;
use NastyaKuznet\Blog\Controller\PostController;
use NastyaKuznet\Blog\Controller\AuthController;
use NastyaKuznet\Blog\Middleware\RoleMiddleware;
use NastyaKuznet\Blog\Middleware\AuthMiddleware;
use NastyaKuznet\Blog\Factory\RoleMiddlewareFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\Routing\RouteCollectorProxy;
use DI\ContainerBuilder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;
use Dotenv\Dotenv;
use NastyaKuznet\Blog\Controller\UserAccountController;

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

$app->get('/', [AuthController::class, 'home']);

// Группировка роутов по префиксу 'post'
$app->group('/post', function (RouteCollectorProxy $group) use ($container) {
    // Роуты, требующие роль 'writer' или выше
    $group->get('/create', [PostController::class, 'create'])->add((new RoleMiddlewareFactory(['writer', 'moderator', 'admin']))($container));
    $group->post('/create', [PostController::class, 'create'])->add((new RoleMiddlewareFactory(['writer', 'moderator', 'admin']))($container));
    // Роуты, требующие роль 'moder' или выше
    $group->get('/edit/{id}', [PostController::class, 'edit'])->add((new RoleMiddlewareFactory(['moderator', 'admin']))($container));
    $group->post('/edit/{id}', [PostController::class, 'edit'])->add((new RoleMiddlewareFactory(['moderator', 'admin']))($container));
});

$app->get('/post', [PostController::class, 'index']);

$app->map(['GET', 'POST'],'/post/{id}', [PostController::class, 'show']);

$app->post('/post/{id}/like', [PostController::class, 'likePost']);

//Заглушки для admins
$app->get('/users', [PostController::class, 'users'])->add((new RoleMiddlewareFactory(['moderator', 'admin']))($container));

$app->get('/account', [UserAccountController::class, 'index'])->add((new RoleMiddlewareFactory(['reader', 'writer', 'moderator', 'admin']))($container));

$app->get('/accounts', function (Request $request, Response $response) {
    $html = "<h1>Главная</h1><ul>
                <li><a href='/account/1'>Личный кабинет (ID 1)</a></li>
                <li><a href='/account/2'>Личный кабинет (ID 2)</a></li>
                <li><a href='/account/3'>Личный кабинет (ID 3)</a></li>
                <li><a href='/account/4'>Личный кабинет (ID 4)</a></li>
                <li><a href='/users'>Пользователи</a></li>
             </ul>";
    $response->getBody()->write($html);
    return $response;
});
/*

// /users (только для админа)
$app->get('/users', function (Request $request, Response $response) use ($databaseService) {
    // Проверяем роль пользователя (фиктивный id=4 это админ)
    $current_user_id = 4;

    $stmt = $databaseService->pdo->query("SELECT u.id, u.nickname, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id");
    $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $databaseService->pdo->query("SELECT role_id FROM users WHERE id = $current_user_id");
    $current_role_id = $stmt->fetchColumn();

    if ($current_role_id != 4) {
        $response->getBody()->write("<h1>Доступ запрещён</h1>");
        return $response->withStatus(403);
    }

    ob_start(); ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Список пользователей</title>
        <link rel="stylesheet" href="/style.css">
    </head>
    <body>
        <h1>Пользователи</h1>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Никнейм</th>
                <th>Роль</th>
                <th>Действия</th>
            </tr>
            <?php foreach ($all_users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['nickname']) ?></td>
                    <td><?= htmlspecialchars($user['role_name']) ?></td>
                    <td>
                        <form method="POST" action="/change_role">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="new_role_id">
                                <option value="1">Читатель</option>
                                <option value="2">Писатель</option>
                                <option value="3">Модератор</option>
                                <option value="4">Администратор</option>
                            </select>
                            <button type="submit">Сохранить</button>
                        </form>
                        <form method="POST" action="/delete_user">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <a href="/">← На главную</a>
    </body>
    </html>
    <?php
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});


// изменения роли пользователя
$app->post('/change_role', function (Request $request, Response $response) use ($databaseService) {
    $parsedBody = $request->getParsedBody();
    $user_id = (int)$parsedBody['user_id'];
    $new_role_id = (int)$parsedBody['new_role_id'];

    $stmt = $databaseService->pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
    $stmt->execute([$new_role_id, $user_id]);

    return $response->withHeader('Location', '/users')->withStatus(302);
});

// удаления пользователя
$app->post('/delete_user', function (Request $request, Response $response, array $args) use ($databaseService) {
    $parsedBody = $request->getParsedBody();
    $user_id = (int)$parsedBody['user_id'];

    if (!isset($parsedBody['user_id']) || !is_numeric($user_id)) {
        die("Некорректный ID пользователя");
    }

    try {
        // Начинаем транзакцию
        $pdo = $databaseService->pdo;
        $pdo->beginTransaction();

        // Проверяем, существует ли пользователь
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_exists = $stmt->fetchColumn();

        if (!$user_exists) {
            throw new Exception("Пользователь не найден");
        }

        // Удаляем все посты пользователя
        $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Удаляем пользователя
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        // Комmitt транзакции
        $pdo->commit();

        return $response->withHeader('Location', '/users')->withStatus(302);
    } catch (PDOException $e) {
        // Откатываем изменения при ошибке
        $pdo->rollBack();
        error_log("Ошибка при удалении пользователя: " . $e->getMessage());
        die("Произошла ошибка при удалении пользователя");
    }
});*/

$app->run(); 
