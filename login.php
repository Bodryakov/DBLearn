<!-- login.php - форма входа в админ-панель с безопасным редиректом -->

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$remember = false;
$saved_login = '';

// Проверка сохраненных данных
if (isset($_COOKIE['remember_me']) && !empty($_COOKIE['remember_me'])) {
    $remember = true;
    $saved_login = $_COOKIE['remember_me'];
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Защита от CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности. Пожалуйста, отправьте форму повторно.';
    } else {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Учетные данные из .env (рекомендуется)
        require_once __DIR__ . '/config.php';
        $ADMIN_LOGIN = $env['ADMIN_LOGIN'] ?? 'admin';
        $ADMIN_PASSWORD = $env['ADMIN_PASSWORD'] ?? 'password';
        
        // Хардкодные учетные данные (только для тестирования)
        // $ADMIN_LOGIN = 'bodryakov.web';
        // $ADMIN_PASSWORD = 'Anna-140275';
        
        if ($login === $ADMIN_LOGIN && $password === $ADMIN_PASSWORD) {
            $_SESSION['authenticated'] = true;
            header('Location: /bod/dashboard');
            exit;
        } else {
            $error = 'Неверный логин или пароль';
            
            // Защита от брутфорса
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            if ($_SESSION['login_attempts'] > 3) {
                sleep(5); // Задержка после нескольких попыток
            }
        }
    }
}

// Генерация CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ-панель</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn {
            padding: 10px 20px;
            background: #1976d2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2196f3;
        }
        .error {
            color: #f44336;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffebee;
            border-radius: 4px;
        }
        .remember-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Вход в админ-панель</h2>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login" class="form-control" 
                       value="<?= htmlspecialchars($remember ? $saved_login : ($_POST['login'] ?? '')) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group remember-group">
                <input type="checkbox" id="remember" name="remember" <?= $remember ? 'checked' : '' ?>>
                <label for="remember">Запомнить меня</label>
            </div>
            
            <button type="submit" class="btn">Войти</button>
        </form>
    </div>

    <script>
        // Восстановление из localStorage (опционально)
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('admin_remember') === '1') {
                const savedLogin = localStorage.getItem('admin_login');
                if (savedLogin) {
                    document.querySelector('[name="login"]').value = savedLogin;
                    document.querySelector('[name="remember"]').checked = true;
                }
            }
            
            // Сохранение в localStorage при отправке формы
            document.querySelector('form').addEventListener('submit', function() {
                if (document.querySelector('[name="remember"]').checked) {
                    localStorage.setItem('admin_remember', '1');
                    localStorage.setItem('admin_login', document.querySelector('[name="login"]').value);
                } else {
                    localStorage.removeItem('admin_remember');
                    localStorage.removeItem('admin_login');
                }
            });
        });
    </script>
</body>
</html>