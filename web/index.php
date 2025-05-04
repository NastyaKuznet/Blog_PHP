<?php
require_once '../src/Base/db.php';
require_once '../src/Base/DatabaseService.php';

use Base\DatabaseService;

// Создаем экземпляр сервиса
$databaseService = new DatabaseService($pdo);

// Пример вывода всех постов
$posts = $databaseService->getAllPosts();
echo "<pre>";
print_r($posts);
echo "</pre>";