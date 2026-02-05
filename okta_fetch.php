#!/usr/bin/env php
<?php
declare(strict_types=1);

// Uzupełnij danymi logowania.
$login = 'YOUR_LOGIN';
$pass = 'YOUR_PASSWORD';

if ($login === 'YOUR_LOGIN' || $pass === 'YOUR_PASSWORD') {
    fwrite(STDERR, "Uzupełnij zmienne login i pass w pliku okta_fetch.php\n");
    exit(1);
}

$authorizeUrl = 'https://arcelik.okta-emea.com/oauth2/v1/authorize?client_id=okta.2b1959c8-bcc0-56eb-a589-cfcfb7422f26&code_challenge=PgpvH9uwEFyESRTVT_Z-F_kNXWsSBz8mdmP0hyk6RGY&code_challenge_method=S256&nonce=57cRjuEV5z48yL08QoutsjdMrHTuHUQxtFptaW9SAnH3746EkCiE2o275MjsNIcl&redirect_uri=https%3A%2F%2Farcelik.okta-emea.com%2Fenduser%2Fcallback&response_type=code&state=CTMr1LoHzERaECK8izTFb0YvFWvsBfGNWSsajxFWuyXAaNYfKsTxfBhDQ26RE7vG&scope=openid%20profile%20email%20okta.users.read.self%20okta.users.manage.self%20okta.internal.enduser.read%20okta.internal.enduser.manage%20okta.enduser.dashboard.read%20okta.enduser.dashboard.manage%20okta.myAccount.sessions.manage%20okta.internal.navigation.enduser.read';
$outputFile = 'okta_page.html';

$python = <<<'PY'
import os
import sys
from playwright.sync_api import sync_playwright, TimeoutError as PlaywrightTimeoutError

login = os.environ["OKTA_LOGIN"]
password = os.environ["OKTA_PASS"]
authorize_url = os.environ["OKTA_AUTHORIZE_URL"]
output_file = os.environ["OKTA_OUTPUT_FILE"]

with sync_playwright() as p:
    browser = p.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    page.goto(authorize_url, wait_until="domcontentloaded", timeout=120000)

    # Pola logowania wskazane przez użytkownika.
    page.fill('input[name="identifier"]', login)
    page.fill('input[name="credentials.passcode"]', password)

    # Kliknięcie przycisku logowania.
    submit_selectors = [
        'input[type="submit"]',
        'button[type="submit"]',
        '#okta-signin-submit',
        'input[data-type="save"]',
        'button[data-type="save"]',
    ]

    submitted = False
    for selector in submit_selectors:
        locator = page.locator(selector)
        if locator.count() > 0:
            locator.first.click()
            submitted = True
            break

    if not submitted:
        raise RuntimeError("Nie znaleziono przycisku logowania.")

    try:
        page.wait_for_load_state("networkidle", timeout=30000)
    except PlaywrightTimeoutError:
        pass

    # Kliknięcie karty aplikacji: Sirius Partner - Beko.
    app_selector = 'a[data-se="app-card"][href="https://arcelik.okta-emea.com/home/bookmark/0oagda9obfeM9qqGs0i7/2557"]'
    app_link = page.locator(app_selector)

    if app_link.count() == 0:
        app_link = page.get_by_role("link", name="uruchom aplikację Sirius Partner - Beko")

    if app_link.count() == 0:
        app_link = page.locator('a[data-se="app-card"]:has-text("Sirius Partner - Beko")')

    if app_link.count() == 0:
        raise RuntimeError("Nie znaleziono kafelka aplikacji Sirius Partner - Beko.")

    app_link.first.click()

    try:
        page.wait_for_load_state("networkidle", timeout=45000)
    except PlaywrightTimeoutError:
        pass

    html = page.content()
    with open(output_file, "w", encoding="utf-8") as f:
        f.write(html)

    context.close()
    browser.close()

print(f"Zapisano treść strony do: {output_file}")
PY;

$tmpPy = tempnam(sys_get_temp_dir(), 'okta_playwright_');
if ($tmpPy === false) {
    fwrite(STDERR, "Nie udało się utworzyć pliku tymczasowego.\n");
    exit(1);
}

if (file_put_contents($tmpPy, $python) === false) {
    fwrite(STDERR, "Nie udało się zapisać skryptu pomocniczego Python.\n");
    @unlink($tmpPy);
    exit(1);
}

$cmd = 'python3 ' . escapeshellarg($tmpPy);
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$env = array_merge($_ENV, [
    'OKTA_LOGIN' => $login,
    'OKTA_PASS' => $pass,
    'OKTA_AUTHORIZE_URL' => $authorizeUrl,
    'OKTA_OUTPUT_FILE' => $outputFile,
]);

$process = proc_open($cmd, $descriptors, $pipes, null, $env);
if (!is_resource($process)) {
    fwrite(STDERR, "Nie udało się uruchomić Pythona.\n");
    @unlink($tmpPy);
    exit(1);
}

fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);

$exitCode = proc_close($process);
@unlink($tmpPy);

if ($exitCode !== 0) {
    fwrite(STDERR, "Błąd podczas automatyzacji logowania.\n");
    if ($stderr !== '') {
        fwrite(STDERR, $stderr);
    }
    exit($exitCode);
}

if ($stdout !== '') {
    echo $stdout;
}
