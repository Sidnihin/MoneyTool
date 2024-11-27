<?php

// Получение данных из запроса
$request = json_decode(file_get_contents('php://input'), true);

// Проверяем существование нужного пути
if ($_SERVER['REQUEST_URI'] === '/converter') {
    // Проверяем, что входные данные корректны
    if (!isset($request['from'], $request['to'], $request['value']) || !is_numeric($request['value'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input data.']);
        exit;
    }

    $from = $request['from'];
    $to = $request['to'];
    $value = (float)$request['value'];

    // Курс валют
    $rates = [
        'USD' => ['EUR' => 0.9, 'GBP' => 0.8],
        'EUR' => ['USD' => 1.1, 'GBP' => 0.89],
    ];

    // Проверяем существование валют
    if (!isset($rates[$from][$to])) {
        http_response_code(400);
        echo json_encode(['error' => 'Currency conversion not supported.']);
        exit;
    }

    // Рассчитываем результат
    $result = $value * $rates[$from][$to];
    echo json_encode(['result' => $result]);
} else {
    // Возвращаем 404, если маршрут не найден
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}
