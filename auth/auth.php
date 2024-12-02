<?php
require 'vendor/autoload.php'; // Подключаем Composer для библиотек (например, firebase/php-jwt)

use \Firebase\JWT\JWT;

$key = "secret_key"; // Ваш секретный ключ для подписи JWT

// Подключение к базе данных PostgreSQL
$dsn = 'pgsql:host=db;dbname=lab_db';
$username = 'user';
$password = 'password';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        // Логика регистрации
        $newUsername = $_POST['username'];
        $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT); // Хешируем пароль

        // Проверка на наличие пользователя
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute(['username' => $newUsername]);
        $userExists = $stmt->fetchColumn();

        if ($userExists) {
            echo "<p style='color: red;'>Пользователь с таким именем уже существует.</p>";
        } else {
            // Добавляем нового пользователя в базу данных с хешированным паролем
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
            $stmt->execute(['username' => $newUsername, 'password_hash' => $newPassword]);
            echo "<p style='color: green;'>Регистрация успешна. Теперь вы можете войти.</p>";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'login') {
        // Логика авторизации
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Проверка пользователя в базе данных
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Проверка, найден ли пользователь и существует ли поле 'password_hash'
        if ($user && isset($user['password_hash']) && password_verify($password, $user['password_hash'])) {
            // Генерация JWT токена
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600;  // 1 час жизни токена
            $payload = array(
                "iat" => $issuedAt,
                "exp" => $expirationTime,
                "username" => $username
            );

            // Добавляем третий аргумент для алгоритма
            $jwt = JWT::encode($payload, $key, 'HS256'); // Третий аргумент для алгоритма

            // Сохраняем JWT в cookies
            setcookie("jwt", $jwt, $expirationTime, "/");

            // Перенаправляем на страницу конвертера
            header("Location: http://localhost:8080");
            exit();
        } else {
            echo "<p style='color: red;'>Неверное имя пользователя или пароль.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация и Регистрация</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .auth-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            font-size: 14px;
            color: #555;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        .error-message {
            color: red;
            font-size: 14px;
        }

        .success-message {
            color: green;
            font-size: 14px;
        }

        .form-container {
            margin-bottom: 30px;
        }

        .form-container h2 {
            font-size: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="form-container">
        <h2>Авторизация</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="username">Имя пользователя:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Войти</button>
        </form>
    </div>

    <div class="form-container">
        <h2>Регистрация</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label for="username">Имя пользователя:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Зарегистрироваться</button>
        </form>
    </div>
</div>

</body>
</html>