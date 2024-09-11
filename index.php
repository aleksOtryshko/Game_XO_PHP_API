<?php
session_start();

// Инициализация игры
if (!isset($_SESSION['board'])) {
    $_SESSION['board'] = [
        ['-', '-', '-'],
        ['-', '-', '-'],
        ['-', '-', '-']
    ];
    $_SESSION['currentPlayer'] = 'X';
    $_SESSION['gameOver'] = false;
    $_SESSION['winner'] = null;
}

// Проверка на конец игры и вывод сообщения о победе
if ($_SESSION['gameOver']) {
    echo "<h2 style='text-align: center;'>Игра окончена! Победитель: {$_SESSION['winner']}</h2>";
} else {
    echo "<h2 style='text-align: center;'>Крестики-нолики</h2>";
    echo "<div id='current-player' style='text-align: center;'>Ходит: {$_SESSION['currentPlayer']}</div>";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Крестики-нолики</title>
    <style>
        table {
            border-collapse: collapse;
            margin: 20px auto;
        }
        td {
            width: 60px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            border: 1px solid #000;
        }
        button {
            width: 100%;
            height: 100%;
            font-size: 24px;
            background-color: transparent;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <table>
        <?php for ($i = 0; $i < 3; $i++): ?>
            <tr>
                <?php for ($j = 0; $j < 3; $j++): ?>
                    <td>
                        <?php if (!$_SESSION['gameOver']): ?>
                            <form method="post" action="dvizhok.php">
                                <input type="hidden" name="gameState" value='<?= json_encode([
                                    'row' => $i,
                                    'col' => $j,
                                    'board' => $_SESSION['board'],
                                    'currentPlayer' => $_SESSION['currentPlayer']
                                ]) ?>'>
                                <button type="submit">
                                    <?= $_SESSION['board'][$i][$j]; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <?= $_SESSION['board'][$i][$j]; ?>
                        <?php endif; ?>
                    </td>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>
    </table>

    <?php if ($_SESSION['gameOver']): ?>
        <div style="text-align: center;">
            <a href="restart.php">Начать заново</a>
        </div>
    <?php endif; ?>
</body>
</html>
