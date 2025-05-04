<?php
include 'db.php';

try {
    $pdo->exec("SELECT 1");
    echo "Подключение к базе данных есть";
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>