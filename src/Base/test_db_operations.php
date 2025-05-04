<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/DatabaseService.php';

use Base\DatabaseService;

// Создаем экземпляр сервиса
$databaseService = new DatabaseService($pdo);

echo "===== Тестирование методов =====\n";

// 1. Добавление нового пользователя
echo "Добавляем пользователя...\n";
if ($databaseService->addUser('john_doe', 'secure_password', 2)) {
    echo "Пользователь успешно добавлен!\n";
} else {
    echo "Ошибка при добавлении пользователя.\n";
}

// 2. Проверка существования пользователя
echo "Проверяем существование пользователя...\n";
$user = $databaseService->checkUser('john_doe', 'secure_password');
if ($user) {
    echo "Пользователь найден: " . print_r($user, true) . "\n";
} else {
    echo "Пользователь не найден.\n";
}

// 3. Получение всех пользователей
echo "Получаем всех пользователей...\n";
$all_users = $databaseService->getAllUsers();
echo "Все пользователи:\n";
print_r($all_users);

// 4. Добавление нового поста
echo "Добавляем новый пост...\n";
$user_id = $user['id']; // ID пользователя, которого мы только что добавили
if ($databaseService->addPost('Мой первый пост', 'Это мой первый пост!', $user_id)) {
    echo "Пост успешно добавлен!\n";
} else {
    echo "Ошибка при добавлении поста.\n";
}

// 5. Получение всех постов
echo "Получаем все посты...\n";
$all_posts = $databaseService->getAllPosts();
echo "Все посты:\n";
print_r($all_posts);

// 6. Редактирование поста
echo "Редактируем пост...\n";
$post_id = $all_posts[0]['id']; // ID первого поста
if ($databaseService->editPost($post_id, 'Обновленный заголовок', 'Обновленное содержимое')) {
    echo "Пост успешно отредактирован!\n";
} else {
    echo "Ошибка при редактировании поста.\n";
}

// 7. Удаление поста
echo "Удаляем пост...\n";
if ($databaseService->deletePost($post_id)) {
    echo "Пост успешно удален!\n";
} else {
    echo "Ошибка при удалении поста.\n";
}

// 8. Удаление пользователя
echo "Удаляем пользователя...\n";
if ($databaseService->deleteUser($user_id)) {
    echo "Пользователь успешно удален!\n";
} else {
    echo "Ошибка при удалении пользователя.\n";
}
?>