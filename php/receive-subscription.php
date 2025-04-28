<?php

session_start();
require_once 'connect-db.php';

ini_set('display_errors', 0);
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['subscription_error'] = "Недопустимый метод запроса";
    header("Location: ../subscription.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['subscription_error'] = "Требуется вход в систему";
    header("Location: ../login.php");
    exit();
}

$keyword = trim($_POST['key-word'] ?? '');
if (mb_strtoupper($keyword) !== 'KIVIN') {
    $_SESSION['subscription_error'] = "Неверное кодовое слово";
    header("Location: ../subscription.php");
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT s.status, s.end_date 
        FROM reader r
        LEFT JOIN subscription s ON r.subscription_id = s.id
        WHERE r.reader_id = ?
        FOR UPDATE
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $subscriptionData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subscriptionData && $subscriptionData['status'] === 'active' && strtotime($subscriptionData['end_date']) > time()) {
        $_SESSION['subscription_error'] = "Активная подписка действительна до ".date('d.m.Y', strtotime($subscriptionData['end_date']));
        $pdo->rollBack();
        header("Location: ../subscription.php");
        exit();
    }

    $stmt = $pdo->prepare("
        INSERT INTO subscription 
            (start_date, end_date, status, price)
        VALUES 
            (NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'active', 300.00)
    ");
    $stmt->execute();
    $newSubscriptionId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        UPDATE reader 
        SET subscription_id = ?
        WHERE reader_id = ?
    ");
    $stmt->execute([$newSubscriptionId, $_SESSION['user_id']]);

    $_SESSION['user_subscription_status'] = 'active';
    $_SESSION['subscription_end_date'] = date('Y-m-d H:i:s', strtotime('+30 days'));

    $pdo->commit();

    $_SESSION['subscription_success'] = "Подписка активна до ".date('d.m.Y', strtotime('+30 days'));
    header("Location: ../LC.php");

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("[".date('Y-m-d H:i:s')."] Subscription Error: ".$e->getMessage().PHP_EOL, 3, __DIR__.'/../logs/error.log');
    $_SESSION['subscription_error'] = "Ошибка обработки запроса";
    header("Location: ../LC.php");
} catch (Throwable $t) {
    error_log("[".date('Y-m-d H:i:s')."] Critical Error: ".$t->getMessage().PHP_EOL, 3, __DIR__.'/../logs/critical.log');
    $_SESSION['subscription_error'] = "Критическая ошибка системы";
    header("Location: ../LC.php");
} finally {
    exit();
}