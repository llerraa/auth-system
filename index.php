<?php
session_start();

// Если залогинен — в профиль, если нет — на логин
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
} else {
    header('Location: login.php');
}
exit;