# gry-agd

dla zabawy

## Sirius Partner redirect flow (PHP)

Skrypt `scraper.php` wykonuje żądanie do strony `https://sirius-partner.beko.com/overview`,
podąża za przekierowaniami i wyświetla wynik końcowy (po wszystkich redirectach).

### Wymagania

- PHP 8+ z włączonym rozszerzeniem `curl`.

### Zmienne środowiskowe

```bash
# opcjonalnie (możesz podmienić URL startowy)
export SIRIUS_OVERVIEW_URL="https://sirius-partner.beko.com/overview"

# opcjonalnie
export OKTA_USER_AGENT="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
```

### Uruchomienie

```bash
php scraper.php
```

### Uwagi

- Skrypt tylko podąża za przekierowaniami i wypisuje wynik końcowy. Nie zapisuje ani nie używa loginu/hasła.
