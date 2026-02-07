# gry-agd

dla zabawy

## Okta web scraping (PHP)

Skrypt `scraper.php` wykonuje logowanie do Okta przez Authn API, pobiera token OAuth2 (PKCE), a następnie uderza w endpoint:
`https://sirius-api.beko.com/Api/Technician/GetTasksDataDetail/...`.

### Wymagania

- PHP 8+ z włączonym rozszerzeniem `curl`.

### Zmienne środowiskowe

Ustaw poniższe zmienne (wymagane są `OKTA_USERNAME` i `OKTA_PASSWORD`):

```bash
export OKTA_USERNAME="twoj_login"
export OKTA_PASSWORD="twoje_haslo"

# opcjonalnie
export OKTA_CLIENT_ID="okta.2b1959c8-bcc0-56eb-a589-cfcfb7422f26"
export OKTA_REDIRECT_URI="https://arcelik.okta-emea.com/enduser/callback"
export OKTA_SCOPE="openid profile email okta.users.read.self okta.users.manage.self okta.internal.enduser.read okta.internal.enduser.manage okta.enduser.dashboard.read okta.enduser.dashboard.manage okta.myAccount.sessions.manage okta.internal.navigation.enduser.read"
export OKTA_DOMAIN="https://arcelik.okta-emea.com"
export OKTA_USER_AGENT="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
```

### Uruchomienie

```bash
php scraper.php \
  --task-id=27790 \
  --offset=0 \
  --from=2025-11-14 \
  --to=2025-11-21 \
  --include-archived=false
```

Parametry CLI są opcjonalne (domyślne wartości odpowiadają temu, co podałeś w linku).

### Uwagi

- Jeśli konto ma MFA lub inne wymagania, Authn API może zwrócić `MFA_REQUIRED` albo inną flagę i skrypt zakończy się komunikatem.
- W takim przypadku trzeba dodać obsługę MFA lub użyć innego flow (np. tokeny serwisowe).
- Skrypt używa `sessionCookieRedirect`, żeby ustawić cookie sesji Okta podobnie do przepływu w przeglądarce, a potem wykonuje `authorize`.
