<?php
session_start();
require_once 'php/connect-db.php'; // Файл с настройками PDO

// Настройка подключения PDO
try {
  $pdo = new PDO(
    'mysql:host=localhost;dbname=projectlibrary;charset=utf8mb4',
    'root',
    '',
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (PDOException $e) {
  die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

// Определяем тип сортировки
$sortTypes = ['alphabetic', 'genre', 'author'];
$sortType = isset($_GET['sort']) && in_array($_GET['sort'], $sortTypes)
  ? $_GET['sort']
  : 'alphabetic';

// Получаем книги с жанрами и авторами
try {
  $query = "SELECT 
                b.*, 
                g.genre_name AS genre_name,
                CONCAT_WS(' ',
                    a.last_name,
                    CONCAT(LEFT(a.first_name, 1), '.'),
                    CONCAT(LEFT(a.middle_name, 1), '.')
                ) AS author_name
              FROM books b
              LEFT JOIN genre g ON b.genre_id = g.id
              LEFT JOIN authors a ON b.author_id = a.id";

  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $books = $stmt->fetchAll();
} catch (PDOException $e) {
  die('Ошибка выполнения запроса: ' . $e->getMessage());
}

// Группировка книг
$groupedBooks = [];
foreach ($books as $book) {
  switch ($sortType) {
    case 'genre':
      $key = $book['genre_name'];
      break;
    case 'author':
      $key = $book['author_name'];
      break;
    default:
      $key = mb_strtoupper(mb_substr($book['title'], 0, 1, 'UTF-8'));
  }
  $groupedBooks[$key][] = $book;
}
ksort($groupedBooks);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta
    name="viewport"
    content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <meta name="description" content="Электронная библиотека M-Library" />
  <title>M-Library</title>
  <link rel="stylesheet" href="assets/css/reset.css" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="assets/css/media.css" />
</head>

<body class="page">
  <header class="header">
    <div class="container container--header">
      <a href="index.php" class="logo">
        <img
          src="assets/img/logolibrary_2.svg"
          alt="M-Library"
          class="logo__logo-image"
          width="229"
          height="65" />
      </a>
      <nav class="nav nav--header">
        <ul class="nav__list" aria-label="menu">
          <li class="nav__item">
            <a href="catalog.php" class="nav__link">Каталог</a>
          </li>
          <li class="nav__item">
            <a href="subscription.php" class="nav__link">Подписка</a>
          </li>
          <li class="nav__item">
            <a href="about.php" class="nav__link">О&nbsp;библиотеке</a>
          </li>
        </ul>
      </nav>
      <div class="search">
        <form class="search__form">
          <label for="nav-search-input" class="search__label visually-hidden">Поиск</label>
          <input
            class="search__input"
            type="search"
            placeholder="Введите название книги или автора"
            id="nav-search-input" />
        </form>
        <button
          type="submit"
          class="search__button"
          aria-label="search-button"
          title="Search">
          <svg
            width="27"
            height="27"
            viewBox="0 0 27 27"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            class="search__icon">
            <g transform="matrix(-1 0 0 1 26 1)">
              <path
                d="M9.30142 0C14.4383 0 18.6028 4.02625 18.6028 8.99261C18.6028 13.959 14.4383 17.9852 9.30142 17.9852C4.16409 17.9852 0 13.959 0 8.99261C0 4.02625 4.16409 6.92328e-07 9.30142 6.92328e-07C9.30142 6.92328e-07 9.30142 0 9.30142 0Z"
                fill="none"
                stroke-width="2"
                stroke="currentColor"
                transform="translate(6.397 0)" />
              <path
                d="M7.8472 0C12.1815 0 15.6948 3.39668 15.6948 7.58667C15.6948 11.7771 12.1815 15.1737 7.8472 15.1737C3.51332 15.1737 0 11.7771 0 7.58667C0 3.39668 3.51332 1.19209e-07 7.8472 1.19209e-07C7.8472 1.19209e-07 7.8472 0 7.8472 0Z"
                transform="translate(7.842 1.32)" />
              <path
                d="M8.79958 0L0 7.7117L2.66774 9.78633L10.1783 1.16597L8.79958 0Z"
                fill="currentColor"
                fill-rule="evenodd"
                stroke-width="2"
                stroke="currentColor"
                transform="translate(0 15.214)" />
            </g>
          </svg>
        </button>
      </div>
      <ul class="nav__list">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav__item">
            <span class="nav__link">Привет, <?= htmlspecialchars($_SESSION['user_email']) ?></span>
          </li>
          <li class="nav__item">
            <a href="php/logout.php" class="nav__link">Выход</a>
          </li>
        <?php else: ?>
          <li class="nav__item">
            <a href="login.php" class="nav__link">Вход</a>
          </li>
          <li class="nav__item">
            <a href="registration.php" class="nav__link">Регистрация</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </header>
  <main class="main">
    <div class="container container--main">
      <h1 class="main__title">Каталог</h1>
      <section class="features-section">
        <header class="features-section__header">
          <h2 class="features-section__section-title">Выберите группировку</h2>
          <form method="GET" class="radio-form">
            <div class="radio">
              <input type="radio" name="sort" value="alphabetic" id="alphabetic-sort"
                <?= $sortType === 'alphabetic' ? 'checked' : '' ?>>
              <label class="radio__input-label" for="alphabetic-sort">По алфавиту</label>

              <input type="radio" name="sort" value="genre" id="genre-sort"
                <?= $sortType === 'genre' ? 'checked' : '' ?>>
              <label class="radio__input-label" for="genre-sort">По жанрам</label>

              <input type="radio" name="sort" value="author" id="author-sort"
                <?= $sortType === 'author' ? 'checked' : '' ?>>
              <label class="radio__input-label" for="author-sort">По авторам</label>
            </div>
          </form>
        </header>
      </section>
      <?php foreach ($groupedBooks as $groupTitle => $booksInGroup): ?>
        <section class="book-section">
          <header class="book-section__header">
            <h2 class="book-section__title">
              <?= htmlspecialchars($groupTitle) ?>
              <?php if ($sortType === 'genre'): ?>
              <?php endif; ?>
            </h2>
          </header>
          <div class="book-section__grid-wrapper">
            <div class="book-section__grid">
              <?php foreach ($booksInGroup as $book): ?>
                <div class="book-card">
                  <img
                    class="book-card__image"
                    src="<?= htmlspecialchars($book['book_cover_url']) ?>"
                    alt="Обложка книги <?= htmlspecialchars($book['title']) ?>"
                    width="180"
                    height="297" />
                  <div class="book-meta">
                    <div class="genre-badge"><?= htmlspecialchars($book['genre_name']) ?></div>
                    <div class="author-info"><?= htmlspecialchars($book['author_name']) ?></div>
                    <button class="button">Читать онлайн</button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
      <?php endforeach; ?>
    </div>
  </main>
  <footer class="footer">
    <div class="container">
      <nav class="nav nav--footer">
        <div class="footer__info">
          <span>&copy; Электронная библиотека M-Library, 2025. Контактный e-mail:
            <a class="footer__email" href="mailto:mLibrary@gmail.com">mLibrary@gmail.com</a></span>
        </div>
        <a class="footer__link" href="for-authors.php">Авторам и правообладателям</a>
        <a class="footer__link" href="conf-policy.php">Политика конфиденциальности</a>
      </nav>
    </div>
  </footer>
  <script>
    document.querySelectorAll('input[name="sort"]').forEach(radio => {
      radio.addEventListener('change', function() {
        this.form.submit();
      });
    });
  </script>
</body>

</html>