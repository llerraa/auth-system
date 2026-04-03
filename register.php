<?php
require_once 'config.php';

$errors = [];
$old_input = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    $old_input = ['name' => $name, 'phone' => $phone, 'email' => $email];
    
    if (empty($name)) {
        $errors['name'] = 'Имя обязательно';
    }
    
    if (empty($phone)) {
        $errors['phone'] = 'Телефон обязателен';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Почта обязательна';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Неверный формат почты';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Пароль обязателен';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Пароль должен быть не менее 6 символов';
    }
    
    if ($password !== $password_confirm) {
        $errors['password_confirm'] = 'Пароли не совпадают';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Этот email уже зарегистрирован';
        }
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $errors['phone'] = 'Этот телефон уже зарегистрирован';
        }
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $hashed_password]);
        header('Location: login.php?registered=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <h2>📝 Регистрация</h2>
        <div class="subtitle">Создайте новый аккаунт</div>
        
        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <?php foreach ($errors as $error): ?>
                    <div class="error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="field">
                <label><i class="fas fa-user"></i> Имя:</label>
                <input type="text" name="name" placeholder="Введите ваше имя" value="<?= htmlspecialchars($old_input['name'] ?? '') ?>">
            </div>
            
            <div class="field">
                <label><i class="fas fa-phone"></i> Телефон:</label>
                <input type="tel" name="phone" placeholder="+7 (999) 123-45-67" value="<?= htmlspecialchars($old_input['phone'] ?? '') ?>">
            </div>
            
            <div class="field">
                <label><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" name="email" placeholder="example@mail.com" value="<?= htmlspecialchars($old_input['email'] ?? '') ?>">
            </div>
            
            <div class="field">
                <label><i class="fas fa-lock"></i> Пароль:</label>
                <input type="password" name="password" placeholder="Минимум 6 символов">
            </div>
            
            <div class="field">
                <label><i class="fas fa-check-circle"></i> Повтор пароля:</label>
                <input type="password" name="password_confirm" placeholder="Введите пароль ещё раз">
            </div>
            
            <button type="submit"><i class="fas fa-user-plus"></i> Зарегистрироваться</button>
        </form>
        
        <div class="link">
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Уже есть аккаунт? Войти</a>
        </div>
    </div>
</body>
</html>