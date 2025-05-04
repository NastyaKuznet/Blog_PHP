<?php //файл для подключения к бд
$host = 'postgres';
$dbname = 'Blog'; 
$username = 'postgres'; 
$password = 'root';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Подключение к базе данных успешно!";
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>