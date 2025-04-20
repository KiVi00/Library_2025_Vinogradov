<?php
// image-upload.php
require_once 'connect-db.php'; // Подключение вашего файла с соединением

// Создаем папку для загрузок, если её нет
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Обработка формы
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Проверяем данные формы
        if (empty($_FILES['image']['name'])) {
            throw new Exception('Выберите файл для загрузки');
        }

        // Валидация файла
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES['image']['tmp_name']);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Недопустимый тип файла');
        }

        // Генерируем уникальное имя файла
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;

        // Перемещаем файл
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            throw new Exception('Ошибка при сохранении файла');
        }

        // Сохраняем в БД
        $stmt = $pdo->prepare("
            INSERT INTO images (file_path, alt_text) 
            VALUES (:file_path, :alt_text)
        ");
        
        $stmt->execute([
            ':file_path' => $targetPath,
            ':alt_text' => $_POST['alt_text'] ?? ''
        ]);

        $message = '<div class="success">Изображение успешно загружено!</div>';

    } catch (Exception $e) {
        $message = '<div class="error">Ошибка: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузка изображений</title>
    <style>
        .container { max-width: 800px; margin: 20px auto; padding: 20px; }
        .success { color: green; padding: 10px; border: 1px solid green; }
        .error { color: red; padding: 10px; border: 1px solid red; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="file"] { width: 100%; padding: 8px; }
        button { padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Загрузка изображений в каталог</h1>
        
        <?= $message ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="image">Выберите изображение (JPEG, PNG, GIF):</label>
                <input type="file" name="image" id="image" accept="image/*" required>
            </div>

            <div class="form-group">
                <label for="alt_text">Описание изображения:</label>
                <input type="text" name="alt_text" id="alt_text" 
                       placeholder="Введите описание изображения">
            </div>

            <button type="submit">Загрузить изображение</button>
        </form>
    </div>
</body>
</html>