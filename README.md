# Tetris z AGD

Prosta gra Tetris, w której klocki zostały zastąpione kultowymi sprzętami AGD.

## Uruchamianie

```bash
python -m http.server 8000
```

Następnie otwórz przeglądarkę na adresie <http://localhost:8000>. Gra
sterowana jest klawiszami strzałek oraz spacją.

## Sterowanie i wskazówki

- <kbd>←</kbd>/<kbd>→</kbd> – przesuwanie spadającego sprzętu w lewo/prawo.
- <kbd>↑</kbd> – obrót aktualnego klocka AGD.
- <kbd>↓</kbd> – miękki zrzut przyśpieszający opad.
- <kbd>Spacja</kbd> – twardy zrzut na dno planszy.
- <kbd>P</kbd> / <kbd>Esc</kbd> – zatrzymanie i wznowienie gry.
- <kbd>Enter</kbd> – rozpoczęcie rozgrywki lub wyjście z pauzy.

Gdy wypełnisz całą linię, sprzęty zostaną zabrane, a wynik i licznik
zebranych linii zaktualizują się automatycznie. Po każdej turze w panelu
po prawej stronie zobaczysz podgląd kolejnego urządzenia, co pozwala lepiej
planować ruchy.

Na starcie i po każdej przegranej wyświetlane jest półprzezroczyste okno z
przyciskiem „Start”, które pozwala rozpocząć nową rundę we własnym tempie.
W dolnym panelu gry znajdziesz także przycisk „Pauza” umożliwiający szybkie
zatrzymanie akcji podczas gry i wznowienie jej jednym kliknięciem.
