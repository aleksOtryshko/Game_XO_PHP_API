import requests
import json

BASE_URL = "http://localhost:8000/index.php"

# Функция для создания новой игры
def start_game():
    payload = {
        "method": "start"
    }
    response = requests.post(BASE_URL, json=payload)

    if response.status_code == 200:
        data = response.json()
        print(f"Игра создана: ID игры - {data['data']['gameId']}")
        return data['data']
    else:
        print(f"Ошибка: {response.json()['data']['error']}")
        return None

# Функция для выполнения хода
def make_move(game_id, row, col, xo):
    payload = {
        "method": "move",
        "gameId": game_id,
        "data": {
            "row": row,
            "col": col,
            "xo": xo
        }
    }

 response = requests.post(BASE_URL, json=payload)

    if response.status_code == 200:
        data = response.json()
        print(f"Текущая доска:")
        for row in data['data']['board']:
            print(" ".join(row))
        if data['data'].get('gameOver'):
            print(f"Игра завершена. Победитель: {data['data'].get('winner', 'Ничья')}")
            return True
        else:
            print(f"Следующий ход: {data['data']['nextTurn']}")
            return False
    else:
        print(f"Ошибка: {response.json()['data']['error']}")
        return False


# Основная логика клиента
if __name__ == "__main__":
    game_data = start_game()

    if game_data:
        game_id = game_data['gameId']

        game_over = False
        while not game_over:
            print("\nВведите ход (номер строки и столбца от 0 до 2, X или O):")
            row = int(input("Строка: "))
            col = int(input("Столбец: "))
            xo = input("X или O: ").upper()

            game_over = make_move(game_id, row, col, xo)
                                                                                                                                                      64,1          Bot

