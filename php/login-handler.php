<?php
session_start();
require_once 'connect-db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Неверный метод запроса");
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    die("Ошибка безопасности CSRF");
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = "Все поля обязательны для заполнения";
    header("Location: ../login.php");
    exit();
}

try {
    // Исправленный запрос с JOIN и выбором статуса
    $stmt = $pdo->prepare("
        SELECT 
            reader.reader_id AS reader_id,
            reader.email,
            reader.first_name,
            reader.last_name,
            reader.password_hash,
            subscription.status AS subscription_status
        FROM reader
        LEFT JOIN subscription 
            ON reader.subscription_id = subscription.id
        WHERE reader.email = ?
    ");
    
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Логирование данных (можно закомментировать в продакшене)
    error_log("User data: " . print_r($user, true));

    if (!$user) {
        $_SESSION['login_error'] = "Пользователь не найден";
        header("Location: ../login.php");
        exit();
    }

    if (!password_verify($password, $user['password_hash'])) {
        $_SESSION['login_error'] = "Неверный пароль";
        header("Location: ../login.php");
        exit();
    }

    // Обновление сессии с данными о подписке
    $_SESSION['user_id'] = $user['reader_id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_last_name'] = $user['last_name'];
    $_SESSION['user_subscription_status'] = $user['subscription_status']; // Новое поле
    
    header("Location: ../index.php");
    exit();

} catch (PDOException $e) {
    error_log("Ошибка БД: " . $e->getMessage());
    $_SESSION['login_error'] = "Ошибка сервера";
    header("Location: ../login.php");
    exit();
}