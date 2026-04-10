<?php
/**
 * Страница регистрации
 */

require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/user.php';

$errors = [];
$success = false;

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    
    // Валидация
    if (strlen($username) < USERNAME_MIN_LENGTH) {
        $errors[] = 'Имя пользователя должно быть не менее ' . USERNAME_MIN_LENGTH . ' символов';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email адрес';
    }
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Пароль должен быть не менее ' . PASSWORD_MIN_LENGTH . ' символов';
    }
    
    if ($password !== $passwordConfirm) {
        $errors[] = 'Пароли не совпадают';
    }
    
    // Если нет ошибок, регистрируем
    if (empty($errors)) {
        $user = new User();
        $result = $user->register($username, $email, $password);
        
        if ($result['success']) {
            $success = true;
            // Автоматический вход после регистрации
            $loginResult = $user->login($username, $password);
            if ($loginResult['success']) {
                header('Location: game.php');
                exit;
            }
        } else {
            $errors[] = $result['message'];
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card card">
        <h2 class="card-title">Регистрация</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Регистрация успешна! Перенаправление...
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="username" class="form-label">Имя пользователя</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    value="<?= htmlspecialchars($username ?? '') ?>"
                    required 
                    minlength="<?= USERNAME_MIN_LENGTH ?>"
                    maxlength="<?= USERNAME_MAX_LENGTH ?>"
                    placeholder="Придумайте имя"
                >
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    value="<?= htmlspecialchars($email ?? '') ?>"
                    required 
                    placeholder="your@email.com"
                >
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    required 
                    minlength="<?= PASSWORD_MIN_LENGTH ?>"
                    placeholder="Минимум 6 символов"
                >
            </div>
            
            <div class="form-group">
                <label for="password_confirm" class="form-label">Подтверждение пароля</label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    class="form-control" 
                    required 
                    placeholder="Повторите пароль"
                >
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-success btn-block">Зарегистрироваться</button>
            </div>
            
            <div class="auth-links">
                <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
            </div>
        </form>
    </div>
</div>

<style>
.auth-container {
    max-width: 500px;
    margin: 40px auto;
    padding: 0 20px;
}

.auth-card {
    padding: 30px;
}

.auth-form {
    margin-top: 20px;
}

.btn-block {
    width: 100%;
    display: block;
}

.auth-links {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid var(--border-color);
}

.auth-links p {
    font-size: 0.6rem;
    color: var(--text-secondary);
}

.auth-links a {
    color: var(--accent-color);
    text-decoration: none;
}

.auth-links a:hover {
    text-decoration: underline;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
    font-size: 0.6rem;
}

.alert li {
    margin-bottom: 5px;
}
</style>

<?php include 'includes/footer.php'; ?>
