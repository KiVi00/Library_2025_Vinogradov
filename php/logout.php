<?php
session_start();
session_unset();
session_destroy();
header("Location: /Library_2025_Vinogradov/index.php");
exit();
