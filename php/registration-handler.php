<?php
session_start();
require_once 'connect-db.php';

// Инициализируем массив ошибок
$_SESSION['registration_errors'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Валидация
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['registration_errors'][] = 'Некорректный формат email';
    }

    if (strlen($password) < 6) {
        $_SESSION['registration_errors'][] = 'Пароль должен быть не менее 6 символов';
    }

    if (empty($_SESSION['registration_errors'])) {
        try {
            // Проверка существующего email
            $stmt = $pdo->prepare("SELECT reader_id FROM reader WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['registration_errors'][] = 'Пользователь с таким email уже существует';
            } else {
                // Регистрация пользователя
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO reader (email, password_hash) VALUES (?, ?)");
                $stmt->execute([$email, $passwordHash]);

                // Очищаем ошибки при успехе
                unset($_SESSION['registration_errors']);
                header("Location: ../login.php?registration=success");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['registration_errors'][] = "Ошибка при регистрации: " . $e->getMessage();
        }
    }
}

// Перенаправляем обратно на форму с ошибками
header("Location: ../registration.php");
exit();
