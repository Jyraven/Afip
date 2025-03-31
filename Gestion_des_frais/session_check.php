<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$public_pages = ['login.php'];

$current_page = basename($_SERVER['PHP_SELF']);

if (!in_array($current_page, $public_pages)) {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }
}
?>