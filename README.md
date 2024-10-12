# Game XO PHP REST API

Это приложение "Крестики-нолики" разработано как пример реализации REST API с использованием JSON для передачи данных. Основная цель проекта — продемонстрировать создание документации по стандарту Swagger и взаимодействие через API. Игра работает через терминал на операционных системах Linux и Windows.

## Описание проекта

Приложение состоит из серверной части на PHP (`index.php`), и двух клиентских программ: одна написана на PHP (`client.php`), другая — на Python (`client.py`). Клиенты взаимодействуют с сервером по HTTP-запросам, отправляя JSON для выполнения ходов в игре.

Сервер обрабатывает следующие запросы:
- Создание новой игры.
- Совершение хода.
- Проверка победителя или ничьей.

Клиенты отправляют запросы с помощью curl (в PHP клиенте) или библиотеки requests (в Python клиенте).

## Установка и запуск

### Требования
- PHP 7.4 или выше.
- Python 3.8 или выше.
- Linux или Windows.
- Установленные расширения `curl` для PHP клиента.

### Установка

1. Клонируйте репозиторий:
   ```bash
   git clone https://github.com/yourusername/tic-tac-toe-api.git
   cd tic-tac-toe-api
Запустите встроенный PHP сервер:

php -S localhost:8000

Это запустит сервер, который будет слушать запросы на порту 8000.

Работа с клиентом на PHP

Запустите PHP клиент:

php client.php

Следуйте инструкциям в терминале, чтобы начать новую игру или сделать ход в уже существующей.

Работа с клиентом на Python

Запустите Python клиент:

python3 client.py

Следуйте инструкциям для создания новой игры и выполнения ходов.

Пример использования

Создание новой игры (POST-запрос)

Метод start отправляется клиентом для создания новой игры:

json

{
  "method": "start"
}

Ответ:

json

{
  "status": 200,
  "data": {
    "gameId": "game_64a8764f8c2a5",
    "board": [
      ["-", "-", "-"],
      ["-", "-", "-"],
      ["-", "-", "-"]
    ],
    "nextTurn": "X"
  }
}

Совершение хода (POST-запрос)

Метод move отправляется для совершения хода:

json

{
  "method": "move",
  "gameId": "game_64a8764f8c2a5",
  "data": {
    "row": 0,
    "col": 1,
    "xo": "X"
  }
}

Ответ:

json

{
  "status": 200,
  "data": {
    "board": [
      ["-", "X", "-"],
      ["-", "-", "-"],
      ["-", "-", "-"]
    ],
    "nextTurn": "O"
  }
}

Особенности разработки

Игра использует JSON: Все данные передаются и принимаются в формате JSON, что делает приложение хорошим примером REST API.

Документация по стандарту Swagger: Игра разработана как часть демонстрации написания документации для REST API по стандарту Swagger. Также в репозитории есть yaml файл по которому можно создать клиента игры на другом языке программирования.

Кросс-платформенность: Приложение тестировалось и работает через терминалы в ОС Linux и Windows.
