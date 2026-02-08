# gry-agd

dla zabawy

## Okta authorize (Chromium, PHP)

Skrypt `scraper.php` uruchamia Chromium w trybie headless,
otwiera podany URL `authorize` i wypisuje zrzut DOM strony po przekierowaniach.

### Wymagania

- PHP 8+.
- Zainstalowany Chromium (np. `chromium` lub `google-chrome`).

### Zmienne środowiskowe

```bash
# opcjonalnie (możesz podmienić URL authorize)
export OKTA_AUTHORIZE_URL="https://arcelik.okta-emea.com/oauth2/aus35952jwgf7NWvK0i7/v1/authorize?..."

# opcjonalnie
export OKTA_USER_AGENT="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"

# opcjonalnie (jeśli chromium ma inną nazwę/ścieżkę)
export CHROMIUM_BIN="/usr/bin/chromium"
```

### Uruchomienie

```bash
php scraper.php
```

### Uwagi

- Skrypt tylko otwiera stronę w Chromium i wypisuje wynik końcowy. Nie zapisuje ani nie używa loginu/hasła.
