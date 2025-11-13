# AGD Repair Tycoon Online

Przeglądarkowa, kooperacyjna gra ekonomiczna o prowadzeniu serwisu AGD. W każdej
rozgrywce tworzysz warsztat naprawiający pralki, zmywarki, kuchenki i lodówki.
Losuj zlecenia dnia, rozwiązuj scenariusze diagnostyczne w formie mini quizów,
zarządzaj energią ekipy oraz inwestuj w ulepszenia zwiększające przychody.

Wersja HTML5 działa w pełni po stronie klienta – stan gry zapisywany jest w
`localStorage`, a tablica warsztatów synchronizuje się w czasie rzeczywistym
między otwartymi kartami dzięki `BroadcastChannel`.

## Uruchomienie lokalne

1. Otwórz plik `web/index.html` w preferowanej przeglądarce lub
2. Uruchom prosty serwer statyczny, aby uzyskać automatyczne odświeżanie:

```bash
python -m http.server 8000 --directory web
```

Następnie przejdź do `http://localhost:8000`.

## Rozgrywka

- Kliknij **„Rozpocznij dzień”**, aby wygenerować nowe zlecenia.
- Przyjmuj interesujące sprawy i rozwiązuj je etapami (diagnoza, naprawa,
  testy) wybierając odpowiedzi w quizach branżowych.
- Każdy etap zużywa energię warsztatu – inwestuj w ulepszenia, by zwiększyć
  liczbę równoległych zleceń, zmniejszyć koszty energii i podnieść reputację.
- Tablica warsztatów wyświetla wyniki Twoje oraz innych graczy otwierających grę
  w tej samej sieci/urządzeniu (działa w oparciu o BroadcastChannel).

Powodzenia w rozbudowie własnego imperium serwisowego!
