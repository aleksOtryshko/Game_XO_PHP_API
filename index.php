<?php
declare(strict_types=1);

// Отправка JSON-ответа
function sendJsonResponse(int $statusCode, array $data): void
{
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode(['status' => $statusCode, 'data' => $data]);
    exit;
}

// Валидация данных для хода
function validateMoveData(array $data): array
{
    $row = $data['row'] ?? null;
    $col = $data['col'] ?? null;
    $xo = $data['xo'] ?? null;

    if (!isset($row, $col, $xo) || !in_array($xo, ['X', 'O']) || !is_int($row) || !is_int($col) || $row < 0 || $row > 2 || $col < 0 || $col > 2) {
        sendJsonResponse(400, ['error' => 'Неверные данные для хода']);
    }

    return ['row' => $row, 'col' => $col, 'xo' => $xo];
}

// Проверка победителя
function checkWinner(array $board): ?string
{
    $lines = array_merge($board, array_map(null, ...$board), [
        [ $board[0][0], $board[1][1], $board[2][2] ],
        [ $board[0][2], $board[1][1], $board[2][0] ]
    ]);

    foreach ($lines as $line) {
        if ($line[0] !== '-' && $line[0] === $line[1] && $line[1] === $line[2]) {
            return $line[0]; // Победитель найден
        }
    }

    return null;
}

// Проверка на ничью
function checkDraw(array $board): bool
{
    foreach ($board as $row) {
        if (in_array('-', $row, true)) {
            return false;
        }
    }
    return true;
}

// Чтение и обработка входящего JSON
$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    sendJsonResponse(400, ['error' => 'Некорректный JSON']);
}

// Чтение и обновление игры
$gameFile = 'game.json';
if (!file_exists($gameFile)) {
    file_put_contents($gameFile, '{}');
}
$games = json_decode(file_get_contents($gameFile), true);

// Обработка методов
$method = $data['method'] ?? null;

if ($method === 'start') {
    $gameId = uniqid('game_', true);
    $games[$gameId] = ['board' => [['-', '-', '-'], ['-', '-', '-'], ['-', '-', '-']], 'nextTurn' => 'X'];
    file_put_contents($gameFile, json_encode($games, JSON_PRETTY_PRINT));

    sendJsonResponse(200, ['gameId' => $gameId, 'board' => $games[$gameId]['board'], 'nextTurn' => $games[$gameId]['nextTurn']]);

} elseif ($method === 'move') {
    $gameId = $data['gameId'] ?? null;
    if (!$gameId || !isset($games[$gameId])) {
        sendJsonResponse(400, ['error' => 'Игра не найдена']);
    }

    $game = $games[$gameId];
    $moveData = validateMoveData($data['data']);

    if ($game['nextTurn'] !== $moveData['xo']) {
        sendJsonResponse(400, ['error' => 'Ход другого игрока']);
    }

    if ($game['board'][$moveData['row']][$moveData['col']] !== '-') {
        sendJsonResponse(400, ['error' => 'Клетка уже занята']);
    }

    $game['board'][$moveData['row']][$moveData['col']] = $moveData['xo'];
    $winner = checkWinner($game['board']);
    if ($winner) {
        sendJsonResponse(200, ['board' => $game['board'], 'gameOver' => true, 'winner' => $winner]);
    }

    if (checkDraw($game['board'])) {
        sendJsonResponse(200, ['board' => $game['board'], 'gameOver' => true, 'winner' => null]);
    }

    $game['nextTurn'] = $moveData['xo'] === 'X' ? 'O' : 'X';
    $games[$gameId] = $game;
    file_put_contents($gameFile, json_encode($games, JSON_PRETTY_PRINT));

    sendJsonResponse(200, ['board' => $game['board'], 'nextTurn' => $game['nextTurn']]);
} else {
    sendJsonResponse(400, ['error' => 'Неизвестный метод']);
}
?>