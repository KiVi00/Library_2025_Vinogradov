<?php
session_start();
require_once 'php/connect-db.php';

$searchQuery = trim(htmlspecialchars($_GET['query'] ?? ''));
$results = [];

if (!empty($searchQuery)) {
  try {
    $sql = "SELECT 
                    b.id,
                    b.title,
                    b.description,
                    b.book_cover_url,
                    b.file_url,
                    b.access_level,
                    CONCAT_WS(' ', a.last_name, a.first_name, a.middle_name) AS author_full_name
                FROM books b
                JOIN authors a ON b.author_id = a.id
                WHERE b.title LIKE :search1
                   OR a.first_name LIKE :search2
                   OR a.last_name LIKE :search3
                   OR a.middle_name LIKE :search4
                   OR CONCAT_WS(' ', a.last_name, a.first_name, a.middle_name) LIKE :search5
                ORDER BY b.title";

    $stmt = $pdo->prepare($sql);
    $searchParam = "%$searchQuery%";
    $stmt->execute([
      'search1' => $searchParam,
      'search2' => $searchParam,
      'search3' => $searchParam,
      'search4' => $searchParam,
      'search5' => $searchParam,
    ]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  } catch (PDOException $e) {
    die("Ошибка поиска: " . $e->getMessage());
  }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <meta name="description" content="Электронная библиотека M-Library" />
  <title>Результаты поиска: "<?= htmlspecialchars($searchQuery) ?>"</title>
  <link rel="stylesheet" href="assets/css/reset.css" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="assets/css/media.css" />
</head>

<body class="page">
  <header class="header">
    <div class="container container--header">
      <a href="index.php" class="logo">
        <img src="assets/img/logolibrary_2.svg" alt="M-Library" class="logo__image" width="229" height="65" />
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
        <form class="search__form" action="search-results.php" method="GET">
          <label for="nav-search-input" class="search__label visually-hidden">Поиск</label>
          <input class="search__input" type="search" placeholder="Введите название книги или автора"
            id="nav-search-input" name="query" />
          <button type="submit" class="search__button" aria-label="search-button" title="Search">
            <svg width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg"
              class="search__icon">
              <g transform="matrix(-1 0 0 1 26 1)">
                <path
                  d="M9.30142 0C14.4383 0 18.6028 4.02625 18.6028 8.99261C18.6028 13.959 14.4383 17.9852 9.30142 17.9852C4.16409 17.9852 0 13.959 0 8.99261C0 4.02625 4.16409 6.92328e-07 9.30142 6.92328e-07C9.30142 6.92328e-07 9.30142 0 9.30142 0Z"
                  fill="none" stroke-width="2" stroke="currentColor" transform="translate(6.397 0)" />
                <path
                  d="M7.8472 0C12.1815 0 15.6948 3.39668 15.6948 7.58667C15.6948 11.7771 12.1815 15.1737 7.8472 15.1737C3.51332 15.1737 0 11.7771 0 7.58667C0 3.39668 3.51332 1.19209e-07 7.8472 1.19209e-07C7.8472 1.19209e-07 7.8472 0 7.8472 0Z"
                  transform="translate(7.842 1.32)" />
                <path d="M8.79958 0L0 7.7117L2.66774 9.78633L10.1783 1.16597L8.79958 0Z" fill="currentColor"
                  fill-rule="evenodd" stroke-width="2" stroke="currentColor" transform="translate(0 15.214)" />
              </g>
            </svg>
          </button>
        </form>
      </div>
      <ul class="nav__list">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav__item">
            <a href="LC.php" class="nav__link">Личный кабинет <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></a>
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
      <h1 class="main__title">Результаты поиска</h1>
      <?php if (!empty($results)): ?>
        <section class="features-section">
          <header class="features-section__header">
            <h2 class="features-section__section-title">
              Результаты по запросу: "<?= htmlspecialchars($searchQuery) ?>"
            </h2>
          </header>
        </section>
        <section class="book-section">
          <header class="book-section__header">
            <h2 class="book-section__title">Найдено <?= count($results) ?>:</h2>
          </header>
          <div class="book-section__grid">
            <?php foreach ($results as $book): ?>
              <div class="book-card">
                <img class="book-card__image"
                  src="<?= htmlspecialchars($book['book_cover_url'] ?? 'assets/img/no-cover.jpg') ?>"
                  alt="Обложка книги: <?= htmlspecialchars($book['title']) ?>" width="202.5" height="297">
                <div class="book-card__book-meta">
                  <h3 class="book-card__title"><?= htmlspecialchars($book['title']) ?></h3>
                  <p class="book-card__author"><?= htmlspecialchars($book['author_full_name']) ?? 'Автор неизвестен' ?></p>
                </div>
                <?php if ($book['access_level'] === 'free'): ?>
                  <a href="<?= htmlspecialchars($book['file_url']) ?>" class="button button--book-card" target="_blank">
                    Читать онлайн
                  </a>
                <?php elseif (isset($_SESSION['user_id']) && isset($_SESSION['user_subscription_status']) && $_SESSION['user_subscription_status'] === 'active'): ?>
                  <!-- Платная книга, но у пользователя есть активная подписка -->
                  <a href="<?= htmlspecialchars($book['file_url']) ?>" class="button button--book-card" target="_blank">
                    Читать онлайн
                  </a>
                <?php else: ?>
                  <!-- Платная книга, но нет подписки -->
                  <a href="subscription.php" class="button button--book-card button--disabled">
                    По подписке
                  </a>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php else: ?>
        <section class="features-section">
          <header class="features-section__header">
            <h2 class="features-section__section-title">
              По запросу: "<?= htmlspecialchars($searchQuery) ?>" ничего не найдено
            </h2>
          </header>
        </section>
      <?php endif; ?>
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
</body>

</html>