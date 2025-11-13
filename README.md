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
- <kbd>Enter</kbd> – rozpoczęcie nowej rundy po przegranej lub wyjście z pauzy.

Gdy wypełnisz całą linię, sprzęty zostaną zabrane, a wynik i licznik
zebranych linii zaktualizują się automatycznie. Po każdej turze w panelu
po prawej stronie zobaczysz podgląd kolejnego urządzenia, co pozwala lepiej
planować ruchy.

Po przegranej lub w trakcie pauzy pojawia się półprzezroczyste okno z
komunikatem. Kliknięcie komunikatu (lub wciśnięcie <kbd>Enter</kbd>) pozwala
szybko wznowić rozgrywkę albo rozpocząć nową rundę. W dolnym panelu gry
znajdziesz także przycisk „Pauza”, dzięki któremu możesz zatrzymać akcję i
wznowić ją jednym kliknięciem.
