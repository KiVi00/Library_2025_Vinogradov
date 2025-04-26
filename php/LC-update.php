<?php
// Включение вывода ошибок (убрать в продакшене)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Старт сессии
session_start();

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Метод не разрешен');
}

// Проверка CSRF-токена
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    die('Недействительный CSRF-токен');
}

// Проверка заполнения полей
$required = ['first_name', 'last_name'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = 'Все поля обязательны для заполнения';
        header('Location: ../profile.php');
        exit;
    }
}

// Подключение к БД
require_once 'connect-db.php';

// Проверка авторизации пользователя
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Требуется авторизация';
    header('Location: ../login.php');
    exit;
}

try {
    // Подготовка SQL-запроса
    $stmt = $pdo->prepare("
        UPDATE reader 
        SET first_name = :first_name, 
            last_name = :last_name 
        WHERE reader_id = :user_id
    ");

    // Выполнение запроса
    $stmt->execute([
        ':first_name' => htmlspecialchars(trim($_POST['first_name'])),
        ':last_name'  => htmlspecialchars(trim($_POST['last_name'])),
        ':user_id'    => $_SESSION['user_id']
    ]);

    // Обновление данных в сессии
    $_SESSION['user_first_name'] = trim($_POST['first_name']);
    $_SESSION['user_last_name'] = trim($_POST['last_name']);

    $_SESSION['success'] = 'Данные успешно обновлены';
    header('Location: ../LC.php');

} catch (PDOException $e) {
    // Обработка ошибок БД
    error_log("Ошибка обновления: " . $e->getMessage());
    $_SESSION['error'] = 'Ошибка при обновлении данных';
    header('Location: ../LC.php');
} finally {
    exit;
}