<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$errors = [];

$stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
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
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Этот email уже используется другим пользователем';
        }
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
        $stmt->execute([$phone, $user_id]);
        if ($stmt->fetch()) {
            $errors['phone'] = 'Этот телефон уже используется другим пользователем';
        }
    }
    
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $errors['new_password'] = 'Пароль должен быть не менее 6 символов';
        } elseif ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Пароли не совпадают';
        }
    }
    
    if (empty($errors)) {
        $sql = "UPDATE users SET name = ?, email = ?, phone = ?";
        $params = [$name, $email, $phone];
        
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $sql .= ", password = ?";
            $params[] = $hashed;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $user_id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $success = 'Данные успешно обновлены';
        $_SESSION['user_name'] = $name;
        $user = ['name' => $name, 'email' => $email, 'phone' => $phone];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Профиль</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <div class="welcome">
                <i class="fas fa-user-circle"></i> Привет, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>!
            </div>
            <a href="logout.php" class="logout-btn" style="background: #e74c3c; color: white; padding: 8px 20px; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-sign-out-alt"></i> Выйти
            </a>
        </div>
        
        <h2><i class="fas fa-id-card"></i> Мой профиль</h2>
        
        <?php if ($success): ?>
            <div class="success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
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
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>">
            </div>
            
            <div class="field">
                <label><i class="fas fa-phone"></i> Телефон:</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
            </div>
            
            <div class="field">
                <label><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            
            <hr>
            
            <h3><i class="fas fa-key"></i> Смена пароля</h3>
            <div class="field">
                <label><i class="fas fa-lock"></i> Новый пароль:</label>
                <input type="password" name="new_password" placeholder="Оставьте пустым, если не хотите менять">
                <small>Минимум 6 символов</small>
            </div>
            
            <div class="field">
                <label><i class="fas fa-check-circle"></i> Подтверждение пароля:</label>
                <input type="password" name="confirm_password" placeholder="Введите пароль ещё раз">
            </div>
            
            <button type="submit"><i class="fas fa-save"></i> Сохранить изменения</button>
        </form>
    </div>
</body>
</html>