<?php

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /Library_2025_Vinogradov/login.php");
    exit();
}
?>