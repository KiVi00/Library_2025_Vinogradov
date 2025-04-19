<?php
session_start();
require_once 'connect-db.php';

$_SESSION['registration_errors'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['registration_errors'][] = 'Некорректный формат email';
    }

    if (strlen($password) < 6) {
        $_SESSION['registration_errors'][] = 'Пароль должен быть не менее 6 символов';
    }

    if (empty($_SESSION['registration_errors'])) {
        try {
            $stmt = $pdo->prepare("SELECT reader_id FROM reader WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['registration_errors'][] = 'Пользователь с таким email уже существует';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO reader (email, password_hash) VALUES (?, ?)");
                $stmt->execute([$email, $passwordHash]);

                unset($_SESSION['registration_errors']);
                header("Location: ../login.php?registration=success");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['registration_errors'][] = "Ошибка при регистрации: " . $e->getMessage();
        }
    }
}

header("Location: ../registration.php");
exit();
