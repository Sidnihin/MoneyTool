<?php
require 'vendor/autoload.php'; // Подключаем Composer для библиотек (например, firebase/php-jwt)

use \Firebase\JWT\JWT;

$key = "secret_key"; // Ваш секретный ключ для подписи JWT

// Функция для декодирования и проверки JWT токена
function validateJWT($jwt, $key) {
    try {
        // Декодируем JWT токен, без передачи заголовков по ссылке
        $decoded = JWT::decode($jwt, $key, ['HS256']);
        return (object) ['valid' => true, 'data' => $decoded];
    } catch (Exception $e) {
        return (object) ['valid' => false, 'message' => $e->getMessage()];
    }
}

// Получаем JWT токен из cookies
$jwt = isset($_COOKIE['jwt']) ? $_COOKIE['jwt'] : '';

// Проверяем, авторизован ли пользователь
$isAuthenticated = false;
$userData = null;

if ($jwt) {
    $validationResult = validateJWT($jwt, $key);
    if ($validationResult->valid) {
        $isAuthenticated = true;
        $userData = $validationResult->data;
    }
}

// Выход из системы: удаляем cookie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    setcookie('jwt', '', time() - 3600, '/');  // Удаляем JWT cookie
    header("Location: /"); // Перенаправляем на главную страницу после выхода
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $value = $_POST['value'];
    $from = $_POST['from'];
    $to = $_POST['to'];

    // Данные для отправки
    $data = [
        'value' => $value,
        'from' => $from,
        'to' => $to
    ];

    // Делаем POST-запрос к микросервису
    $url = 'http://converter:8002/converter';  // Используем новый адрес микросервиса
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode($data),
        ],
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        $error = 'Ошибка при конвертации валют.';
    } else {
        $responseData = json_decode($response, true);
        $result = isset($responseData['result']) ? $responseData['result'] : null;
        $error = isset($responseData['error']) ? $responseData['error'] : null;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Конвертер валют</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .auth-container, .converter-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .auth-button, .logout-button, button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .auth-button:hover, .logout-button:hover, button[type="submit"]:hover {
            background-color: #45a049;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            font-size: 14px;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        #result {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .user-info {
            margin-bottom: 20px;
            font-size: 18px;
            color: #333;
        }
    </style>
</head>
<body>

<?php if (!$isAuthenticated): ?>
    <div class="auth-container">
        <h1>Пожалуйста, авторизуйтесь для использования сервиса</h1>
        <button class="auth-button" onclick="window.location.href='http://localhost:8001'">Регистрация</button>
    </div>
<?php else: ?>
    <!-- Выводим логин пользователя -->
    <div class="user-info">
        <h2>Добро пожаловать, <?php echo htmlspecialchars($userData->username); ?>!</h2>
    </div>

    <!-- Кнопка выхода -->
    <form method="POST">
        <button class="logout-button" type="submit" name="logout">Выйти</button>
    </form>

    <div class="converter-container">
        <h1>Конвертер валют</h1>
        <form id="currencyForm" method="POST">
            <div class="form-group">
                <label for="value">Сумма:</label>
                <input type="text" name="value" id="value" placeholder="Введите сумму" required>
            </div>

            <div class="form-group">
                <label for="from">Из валюты:</label>
                <select name="from" id="from" required>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                </select>
            </div>

            <div class="form-group">
                <label for="to">В валюту:</label>
                <select name="to" id="to" required>
                    <option value="EUR">EUR</option>
                    <option value="USD">USD</option>
                </select>
            </div>

            <button type="submit">Конвертировать</button>
        </form>

        <div id="result">
            <?php
            if (isset($result)) {
                echo "Результат: " . $result;
            } elseif (isset($error)) {
                echo "Ошибка: " . $error;
            }
            ?>
        </div>
    </div>
<?php endif; ?>

<script>
    document.getElementById('currencyForm').onsubmit = function(event) {
        event.preventDefault();

        const jwtToken = document.cookie.replace(/(?:(?:^|.*;\s*)jwt\s*\=\s*([^;]*).*$)|^.*$/, "$1");


</script>

</body>
</html>