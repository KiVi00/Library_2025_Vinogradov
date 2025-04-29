<?php
// book_access_button.php
if($book['access_level'] === 'free') {
    echo '<a href="'.htmlspecialchars($book['file_url']).'" class="button button--book-card" target="_blank">
            Читать онлайн
          </a>';
} elseif(isset($_SESSION['user_subscription_status']) && $_SESSION['user_subscription_status'] === 'active') {
    echo '<a href="'.htmlspecialchars($book['file_url']).'" class="button button--book-card" target="_blank">
            Читать онлайн
          </a>';
} else {
    echo '<a href="subscription.php" class="button button--book-card button--disabled">
            По подписке
          </a>';
}
?>