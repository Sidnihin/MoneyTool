<?php

// Подключение к базе данных
$pdo = new PDO("pgsql:host=db;dbname=lab_db", "user", "password");

// Функция для обработки регистрации
function register($pdo, $username, $password) {
    // Хеширование пароля
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Подготовка запроса на вставку нового пользователя
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password)");
    $stmt->execute(['username' => $username, 'password' => $passwordHash]);

    return true; // Успешная регистрация
}

// Функция для обработки входа
function login($pdo, $username, $password) {
    // Получаем пользователя по логину
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Проверка, что пользователь существует и пароль верен
    if ($user && password_verify($password, $user['password_hash'])) {
        return true; // Успешный вход
    } else {
        return false; // Неверные данные
    }
}

// Обработка форм
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Регистрация
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $success = register($pdo, $_POST['username'], $_POST['password']);
            if ($success) {
                echo "<p>Регистрация прошла успешно! <a href='/login'>Войти</a></p>";
            } else {
                echo "<p>Ошибка регистрации</p>";
            }
        } else {
            echo "<p>Заполните все поля для регистрации</p>";
        }
    } elseif (isset($_POST['login'])) {
        // Авторизация
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $success = login($pdo, $_POST['username'], $_POST['password']);
            if ($success) {
                // Перенаправляем на главную страницу
                header('Location: http://localhost:8080');
                exit; // Завершаем выполнение скрипта после редиректа
            } else {
                echo "<p>Неверные данные для входа</p>";
            }
        } else {
            echo "<p>Заполните все поля для входа</p>";
        }
    }
} else {
    // HTML форма
    echo '
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Регистрация и Вход</title>
    </head>
    <body>
        <h2>Регистрация</h2>
        <form method="POST">
            <label for="username">Имя пользователя:</label><br>
            <input type="text" id="username" name="username" required><br><br>
            <label for="password">Пароль:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <input type="submit" name="register" value="Зарегистрироваться">
        </form>
        
        <h2>Вход</h2>
        <form method="POST">
            <label for="username">Имя пользователя:</label><br>
            <input type="text" id="username" name="username" required><br><br>
            <label for="password">Пароль:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <input type="submit" name="login" value="Войти">
        </form>
    </body>
    </html>
    ';
}

