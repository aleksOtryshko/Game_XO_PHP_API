<?php
declare(strict_types=1);

// Отправка JSON-запроса
function sendRequest(string $url, array $payload): array
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);

    if ($response === false) {
        die('Ошибка запроса: ' . curl_error($ch));
    }

    curl_close($ch);
    return json_decode($response, true);
}

// Удаление файла client_game.json
function deleteClientGameFile(string $filePath): void
{
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "Файл $filePath успешно удален.\n";
    } else {
        echo "Файл $filePath не найден.\n";
    }
}

$clientGameFile = 'client_game.json';

// Если файл игры не существует, создаем новую игру
if (!file_exists($clientGameFile)) {
    echo "Запрашиваем создание новой игры...\n";
    $response = sendRequest('http://localhost:8000/index.php', ['method' => 'start']);

    if ($response['status'] === 200) {
        file_put_contents($clientGameFile, json_encode([$response['data']['gameId'] => $response['data']], JSON_PRETTY_PRINT));
        echo "Новая игра создана. ID игры: " . $response['data']['gameId'] . "\n";
    } else {
        die("Ошибка создания игры: " . json_encode($response));
    }
} else {
    // Читаем данные из файла client_game.json
    $gamesData = json_decode(file_get_contents($clientGameFile), true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($gamesData)) {
        die('Ошибка данных: некорректный формат JSON.');
    }

    if (empty($gamesData)) {
        echo "Активных игр нет.\n";
        deleteClientGameFile($clientGameFile);
        exit;
    }

    // Вывод состояния игр
    echo "Состояние активных игр:\n";
    foreach ($gamesData as $gameId => $game) {
        if (!isset($game['board'], $game['nextTurn'])) {
            echo "Ошибка данных для игры $gameId\n";
            continue;
        }

        echo "Игра ID: $gameId\n";
        foreach ($game['board'] as $row) {
            echo implode(' | ', $row) . "\n";
        }
        echo "Следующий ход: " . $game['nextTurn'] . "\n\n";
    }

    // Ввод данных для хода
    echo "Введите ID игры: ";
    $gameId = trim(fgets(STDIN));

    if (!isset($gamesData[$gameId])) {
        die("Игра с ID $gameId не найдена.\n");
    }

    echo "Введите строку (0-2): ";
    $row = (int)trim(fgets(STDIN));

    echo "Введите столбец (0-2): ";
    $col = (int)trim(fgets(STDIN));

    echo "Введите знак (X или O): ";
    $xo = trim(fgets(STDIN));

    // Отправка хода на сервер
    $response = sendRequest('http://localhost:8000/index.php', [
        'method' => 'move',
        'gameId' => $gameId,
        'data' => ['row' => $row, 'col' => $col, 'xo' => $xo]
    ]);

    if ($response['status'] === 200) {
        $gamesData[$gameId] = $response['data'];
        file_put_contents($clientGameFile, json_encode($gamesData, JSON_PRETTY_PRINT));
        echo "Ход успешно выполнен!\n";
    } else {
        die("Ошибка совершения хода: " . json_encode($response));
    }
}
?>
