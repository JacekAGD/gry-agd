# gry-agd

dla zabawy

## Okta redirect flow (PHP)

Skrypt `scraper.php` wykonuje żądanie do podanego URL `authorize`, podąża za przekierowaniami i wyświetla wynik końcowy.
Jeżeli w treści odpowiedzi pojawi się link do bookmarka Okta, skrypt otworzy go i wypisze wynik końcowy tego wywołania.

### Wymagania

- PHP 8+ z włączonym rozszerzeniem `curl`.

### Zmienne środowiskowe

```bash
# opcjonalnie (możesz podmienić URL authorize)
export OKTA_AUTHORIZE_URL="https://arcelik.okta-emea.com/oauth2/v1/authorize?..."

# opcjonalnie
export OKTA_USER_AGENT="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
```

### Uruchomienie

```bash
php scraper.php
```

### Uwagi

- Skrypt tylko podąża za przekierowaniami i wypisuje wynik końcowy. Nie zapisuje ani nie używa loginu/hasła.
- Jeżeli w odpowiedzi pojawi się link do bookmarka Okta `https://arcelik.okta-emea.com/home/bookmark/0oagda9obfeM9qqGs0i7/2557`, skrypt wywoła go i wyświetli wynik po przekierowaniach.
