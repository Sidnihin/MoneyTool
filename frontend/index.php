<?php
// Получаем данные из формы
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
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .converter-container {
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border-radius: 8px;
            width: 400px;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            text-align: left;
            margin-bottom: 5px;
            font-size: 16px;
            color: #555;
        }

        input, select, button {
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        input[type="text"] {
            width: 100%;
            box-sizing: border-box;
        }

        select {
            width: 100%;
            box-sizing: border-box;
        }

        .form-group {
            margin-bottom: 20px;
        }

        #result {
            margin-top: 20px;
            font-weight: bold;
        }

        /* Стили для кнопки регистрации */
        .auth-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 4px;
        }

        .auth-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<button class="auth-button" onclick="window.location.href='http://localhost:8001'">Регистрация</button>

<div class="converter-container">
    <h1>Конвертер валют</h1>
    <form method="POST" action="">
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

</body>
</html>