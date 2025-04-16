<?php
session_start();
require_once 'connect-db.php';

// Включение детального вывода ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Неверный метод запроса");
}

// Проверка CSRF-токена
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    die("Ошибка безопасности CSRF");
}

// Получение и очистка данных
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Базовые проверки
if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = "Все поля обязательны для заполнения";
    header("Location: ../login.php");
    exit();
}

try {
    // Поиск пользователя
    $stmt = $pdo->prepare("SELECT * FROM reader WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("User data: " . print_r($user, true));
    error_log("Input password: " . $password);
    
    if ($user) {
        error_log("Stored hash: " . $user['password_hash']);
        error_log("Verification result: " . password_verify($password, $user['password_hash']));
    }

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

    $_SESSION['user_id'] = $user['reader_id'];
    $_SESSION['user_email'] = $user['email'];
    
    header("Location: ../index.php");
    exit();

} catch (PDOException $e) {
    error_log("Ошибка БД: " . $e->getMessage());
    $_SESSION['login_error'] = "Ошибка сервера";
    header("Location: ../login.php");
    exit();
}