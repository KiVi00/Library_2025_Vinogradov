<?php
// receive-subscription.php

session_start();
require_once 'connect-db.php';

// Включение отладки (убрать в продакшене)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['subscription_error'] = "Неверный метод запроса";
    header("Location: ../personal-cabinet.php");
    exit();
}

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    $_SESSION['subscription_error'] = "Требуется авторизация";
    header("Location: ../login.php");
    exit();
}

// Проверка CSRF-токена (добавьте скрытое поле в форму если нужно)
// if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//     $_SESSION['subscription_error'] = "Ошибка безопасности";
//     header("Location: ../personal-cabinet.php");
//     exit();
// }

// Валидация ключевого слова
$keyword = trim($_POST['key-word'] ?? '');
if ($keyword !== 'KiViN') {
    $_SESSION['subscription_error'] = "Неверное ключевое слово";
    header("Location: ../personal-cabinet.php");
    exit();
}

try {
    // Начало транзакции
    $pdo->beginTransaction();

    // Создание новой подписки
    $stmt = $pdo->prepare("
        INSERT INTO subscription 
            (start_date, end_date, status, price)
        VALUES 
            (NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), 'active', 300.00)
    ");
    $stmt->execute();
    $subscriptionId = $pdo->lastInsertId();

    // Обновление подписки пользователя
    $stmt = $pdo->prepare("
        UPDATE reader 
        SET subscription_id = :subscription_id 
        WHERE reader_id = :user_id
    ");
    $stmt->execute([
        ':subscription_id' => $subscriptionId,
        ':user_id' => $_SESSION['user_id']
    ]);

    // Обновление данных в сессии
    $_SESSION['user_subscription_status'] = 'active';

    // Фиксация транзакции
    $pdo->commit();

    $_SESSION['subscription_success'] = "Подписка успешно активирована!";
    header("Location: ../LC.php");

} catch (PDOException $e) {
    // Откат транзакции при ошибке
    $pdo->rollBack();
    
    error_log("Ошибка активации подписки: " . $e->getMessage());
    $_SESSION['subscription_error'] = "Ошибка при активации подписки";
    header("Location: ../LC.php");
} catch (Exception $e) {
    error_log("Общая ошибка: " . $e->getMessage());
    $_SESSION['subscription_error'] = "Системная ошибка";
    header("Location: ../LC.php");
} finally {
    exit();
}