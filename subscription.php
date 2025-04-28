<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="description" content="Электронная библиотека M-Library" />
    <title>Подписка</title>
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
                                <path d="M8.79958 0L0 7.7117L2.66774 9.78633L10.1783 1.16597L8.79958 0Z"
                                    fill="currentColor" fill-rule="evenodd" stroke-width="2" stroke="currentColor"
                                    transform="translate(0 15.214)" />
                            </g>
                        </svg>
                    </button>
                </form>
            </div>
            <ul class="nav__list">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav__item">
                        <a href="LC.php" class="nav__link">Личный кабинет
                            <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></a>
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
            <h1 class="main__title">Подписка</h1>
            <section class="features-section">
                <header class="features-section__header">
                    <?php if (!empty($_SESSION['subscription_error'])): ?>
                        <div class="error-message">
                            <?= htmlspecialchars($_SESSION['subscription_error']) ?>
                        </div>
                    <?php endif; ?>
                    <h2 class="features-section__section-title">
                        Для приобретения подписки введите KiViN и нажмите на кнопку "Получить подписку"
                    </h2>
                    <form action="php/receive-subscription.php" method="POST" class="form">
                        <label for="key-word" class="form__input-label">Ключевое слово</label>
                        <input class="form__text-input" type="text" maxlength="5" name="key-word"
                            placeholder="Введите ключевое слово" id="key-word">
                        <button type="submit" class="button button--form">Получить подписку</button>
                    </form>
                </header>
            </section>
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