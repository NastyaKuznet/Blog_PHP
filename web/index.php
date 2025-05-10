<?php
require_once '../vendor/autoload.php';
require_once '../src/Base/db.php';
require_once '../src/Base/DatabaseService.php';

use Base\DatabaseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// Подключение к БД
$pdo = new PDO("pgsql:host=postgres;dbname=Blog", 'postgres', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$databaseService = new DatabaseService($pdo);

$databaseService = new DatabaseService($pdo);
$databaseService->createTestUsers();

$app = AppFactory::create();

//главная страница
$app->get('/', function (Request $request, Response $response) {
    $html = "<h1>Главная</h1><ul>
                <li><a href='/account/4'>Личный кабинет (ID 4)</a></li>
                <li><a href='/users'>Пользователи</a></li>
             </ul>";
    $response->getBody()->write($html);
    return $response;
});

// /account/{id}
$app->get('/account/{id}', function (Request $request, Response $response, array $args) use ($databaseService) {
    $user_id = (int)$args['id'];

    $stmt = $databaseService->pdo->prepare("SELECT u.id, u.nickname, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_info) {
        $response->getBody()->write("<h1>Пользователь не найден</h1>");
        return $response->withStatus(404);
    }

    $stmt = $databaseService->pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_start(); ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Личный кабинет</title>
    </head>
    <body>
        <h1>Личный кабинет</h1>
        <p><strong>Имя пользователя:</strong> <?= htmlspecialchars($user_info['nickname']) ?></p>
        <p><strong>Роль:</strong> <?= htmlspecialchars($user_info['role_name']) ?></p>

        <?php if ($user_info['role_name'] !== 'reader'): ?>
            <h2>Посты пользователя</h2>
            <ul>
                <?php foreach ($posts as $post): ?>
                    <li>
                        <strong><?= htmlspecialchars($post['title']) ?></strong><br>
                        <?= nl2br(htmlspecialchars($post['content'])) ?><br>
                        <small><?= htmlspecialchars($post['created_at']) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <a href="/">← На главную</a>
    </body>
    </html>
    <?php
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});

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

    $stmt = $databaseService->pdo->prepare("DELETE FROM posts WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $stmt = $databaseService->pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    return $response->withHeader('Location', '/users')->withStatus(302);
});

$app->run();