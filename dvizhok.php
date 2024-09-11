<?php
session_start();

// Проверяем, получен ли JSON с состоянием игры
if (isset($_POST['gameState']) && !$_SESSION['gameOver']) {
    // Декодируем JSON с данными о состоянии игры
    $gameState = json_decode($_POST['gameState'], true);

    $row = (int)$gameState['row'];
    $col = (int)$gameState['col'];
    $board = $gameState['board'];
    $currentPlayer = $gameState['currentPlayer'];

    // Проверяем, свободна ли клетка
    if ($board[$row][$col] === '-') {
        // Обновляем клетку текущим игроком
        $board[$row][$col] = $currentPlayer;

        // Обновляем состояние игры в сессии
        $_SESSION['board'] = $board;

        // Проверяем победителя
        if (checkWinner($board, $currentPlayer)) {
            $_SESSION['gameOver'] = true;
            $_SESSION['winner'] = $currentPlayer;
        } elseif (checkDraw($board)) {
            // Если ничья
            $_SESSION['gameOver'] = true;
            $_SESSION['winner'] = 'Дружба';
        } else {
            // Переключаем игрока, если никто не победил и нет ничьей
            $_SESSION['currentPlayer'] = $currentPlayer === 'X' ? 'O' : 'X';
        }
    }
}

// Функция проверки победителя
function checkWinner($board, $player) {
    // Проверяем строки, столбцы и диагонали
    for ($i = 0; $i < 3; $i++) {
        if ($board[$i][0] === $player && $board[$i][1] === $player && $board[$i][2] === $player) {
            return true;  // Победа по строке
        }
        if ($board[0][$i] === $player && $board[1][$i] === $player && $board[2][$i] === $player) {
            return true;  // Победа по столбцу
        }
    }
    // Проверка диагоналей
    if ($board[0][0] === $player && $board[1][1] === $player && $board[2][2] === $player) {
        return true;  // Победа по главной диагонали
    }
    if ($board[0][2] === $player && $board[1][1] === $player && $board[2][0] === $player) {
        return true;  // Победа по побочной диагонали
    }
    return false;  // Нет победителя
}

// Функция проверки ничьей
function checkDraw($board) {
    foreach ($board as $row) {
        if (in_array('-', $row)) {
            return false; // Есть хотя бы одна пустая клетка, продолжаем игру
        }
    }
    return true; // Все клетки заполнены, но победителя нет
}

// Возвращаемся на основную страницу
header("Location: index.php");
exit();
?>
