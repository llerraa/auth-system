<?php
require_once 'config.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: profile.php');
            exit;
        } else {
            $error = 'Неверный логин/телефон или пароль';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Авторизация</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2>🔐 Авторизация</h2>
        <div class="subtitle">Войдите в свой аккаунт</div>
        
        <?php if ($error): ?>
            <div class="error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['registered'])): ?>
            <div class="success"><i class="fas fa-check-circle"></i> Регистрация успешна! Теперь войдите.</div>
        <?php endif; ?>
        
        <form method="post">
            <div class="field">
                <label><i class="fas fa-envelope"></i> Телефон или Email:</label>
                <input type="text" name="login" placeholder="example@mail.com или +7 (999) 123-45-67" required>
            </div>
            
            <div class="field">
                <label><i class="fas fa-lock"></i> Пароль:</label>
                <input type="password" name="password" placeholder="Введите пароль" required>
            </div>
            
            <button type="submit"><i class="fas fa-sign-in-alt"></i> Войти</button>
        </form>
        
        <div class="link">
            <a href="register.php"><i class="fas fa-user-plus"></i> Нет аккаунта? Зарегистрироваться</a>
        </div>
    </div>
</body>
</html>