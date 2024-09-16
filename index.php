<?php

// Файл для хранения состояния игры
$gameFile = 'game_state.json';

// Проверяем метод запроса
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Обработка GET-запроса для получения текущего состояния игры

    // Инициализация игры при первом запуске
    if (!file_exists($gameFile)) {
        $board = [
            ['-', '-', '-'],
            ['-', '-', '-'],
            ['-', '-', '-']
        ];
        $currentPlayer = 'X';
        $gameOver = false;
        $winner = null;

        // Запись начального состояния игры в файл
        file_put_contents($gameFile, json_encode([
            'board' => $board,
            'currentPlayer' => $currentPlayer,
            'gameOver' => $gameOver,
            'winner' => $winner
        ]));
    } else {
        // Загрузка состояния игры из файла
        $gameData = json_decode(file_get_contents($gameFile), true);
        $board = $gameData['board'];
        $currentPlayer = $gameData['currentPlayer'];
        $gameOver = $gameData['gameOver'];
        $winner = $gameData['winner'];
    }

    // Ответ с текущим состоянием игры
    $response = [
        'status' => 200,
        'data' => [
            'board' => $board,
            'currentPlayer' => $currentPlayer,
            'gameOver' => $gameOver ? $winner : null
        ]
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} elseif ($method === 'POST') {
    // Обработка POST-запроса для выполнения хода

    // Получение JSON из тела запроса
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Проверка ошибок при декодировании JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        header('Content-Type: application/json', true, 400);
        echo json_encode([
            'status' => 400,
            'message' => 'Некорректный формат JSON'
        ]);
        exit();
    }

    // Валидация данных
    if (!isset($data['row']) || !isset($data['col']) || !is_int($data['row']) || !is_int($data['col']) ||
        !isset($data['board']) || !isset($data['currentPlayer'])) {
        header('Content-Type: application/json', true, 400);
        echo json_encode([
            'status' => 400,
            'message' => 'Неверные данные. Требуются: row, col, board, currentPlayer.'
        ]);
        exit();
    }

    // Проверка, что ход находится в пределах доски
    $row = $data['row'];
    $col = $data['col'];
    $board = $data['board'];
    $currentPlayer = $data['currentPlayer'];

    if ($row < 0 || $row > 2 || $col < 0 || $col > 2) {
        header('Content-Type: application/json', true, 400);
        echo json_encode([
            'status' => 400,
            'message' => 'Неверные координаты. Должны быть в пределах 0-2.'
        ]);
        exit();
    }

    // Загрузка текущего состояния игры
    $gameData = json_decode(file_get_contents($gameFile), true);
    $storedBoard = $gameData['board'];
    $storedPlayer = $gameData['currentPlayer'];
    $gameOver = $gameData['gameOver'];

    // Проверка, что клетка свободна
    if ($storedBoard[$row][$col] !== '-') {
        header('Content-Type: application/json', true, 400);
        echo json_encode([
            'status' => 400,
            'message' => 'Клетка уже занята.'
        ]);
        exit();
    }

    // Проверка правильности игрока (поочередность ходов)
    if ($currentPlayer !== $storedPlayer) {
        header('Content-Type: application/json', true, 400);
        echo json_encode([
            'status' => 400,
            'message' => 'Сейчас не ваш ход. Ходит игрок: ' . $storedPlayer
        ]);
        exit();
    }

    // Запись хода текущего игрока
    $storedBoard[$row][$col] = $currentPlayer;

    // Проверка победителя
    if (checkWinner($storedBoard, $currentPlayer)) {
        header('Content-Type: application/json', true, 200);
        file_put_contents($gameFile, json_encode([
            'board' => [
                ['-', '-', '-'],
                ['-', '-', '-'],
                ['-', '-', '-']
            ],
            'currentPlayer' => 'X',
            'gameOver' => true,
            'winner' => $currentPlayer
        ]));
        echo json_encode([
            'status' => 200,
            'data' => [
                'board' => $storedBoard,
                'gameOver' => $currentPlayer
            ]
        ]);
        exit();
    }

    // Проверка на ничью
    if (isDraw($storedBoard)) {
        header('Content-Type: application/json', true, 200);
        file_put_contents($gameFile, json_encode([
            'board' => [
                ['-', '-', '-'],
                ['-', '-', '-'],
                ['-', '-', '-']
            ],
            'currentPlayer' => 'X',
            'gameOver' => true,
            'winner' => 'Draw'
        ]));
        echo json_encode([
            'status' => 200,
            'data' => [
                'board' => $storedBoard,
                'gameOver' => 'Draw'
            ]
        ]);
        exit();
    }

    // Смена игрока
    $nextPlayer = $currentPlayer === 'X' ? 'O' : 'X';

    // Сохранение обновленного состояния игры
    file_put_contents($gameFile, json_encode([
        'board' => $storedBoard,
        'currentPlayer' => $nextPlayer,
        'gameOver' => false,
        'winner' => null
    ]));

    header('Content-Type: application/json', true, 200);
    echo json_encode([
        'status' => 200,
        'data' => [
            'board' => $storedBoard,
            'currentPlayer' => $nextPlayer,
            'gameOver' => null
        ]
    ]);
}

// Функция проверки победителя
function checkWinner($board, $player) {
    $winningCombinations = [
        [[0, 0], [0, 1], [0, 2]],
        [[1, 0], [1, 1], [1, 2]],
        [[2, 0], [2, 1], [2, 2]],
        [[0, 0], [1, 0], [2, 0]],
        [[0, 1], [1, 1], [2, 1]],
        [[0, 2], [1, 2], [2, 2]],
        [[0, 0], [1, 1], [2, 2]],
        [[0, 2], [1, 1], [2, 0]],
    ];

    foreach ($winningCombinations as $combination) {
        if ($board[$combination[0][0]][$combination[0][1]] === $player &&
            $board[$combination[1][0]][$combination[1][1]] === $player &&
            $board[$combination[2][0]][$combination[2][1]] === $player) {
            return true;
        }
    }
    return false;
}

// Функция проверки на ничью
function isDraw($board) {
    foreach ($board as $row) {
        if (in_array('-', $row)) {
            return false;
        }
    }
    return true;
}

?>