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

$baseUrl = 'https://arcelik.okta-emea.com';
$authorizeUrl = 'https://arcelik.okta-emea.com/oauth2/v1/authorize?client_id=okta.2b1959c8-bcc0-56eb-a589-cfcfb7422f26&code_challenge=PgpvH9uwEFyESRTVT_Z-F_kNXWsSBz8mdmP0hyk6RGY&code_challenge_method=S256&nonce=57cRjuEV5z48yL08QoutsjdMrHTuHUQxtFptaW9SAnH3746EkCiE2o275MjsNIcl&redirect_uri=https%3A%2F%2Farcelik.okta-emea.com%2Fenduser%2Fcallback&response_type=code&state=CTMr1LoHzERaECK8izTFb0YvFWvsBfGNWSsajxFWuyXAaNYfKsTxfBhDQ26RE7vG&scope=openid%20profile%20email%20okta.users.read.self%20okta.users.manage.self%20okta.internal.enduser.read%20okta.internal.enduser.manage%20okta.enduser.dashboard.read%20okta.enduser.dashboard.manage%20okta.myAccount.sessions.manage%20okta.internal.navigation.enduser.read';

$payload = json_encode([
    'username' => $login,
    'password' => $pass,
    'options' => [
        'warnBeforePasswordExpired' => true,
        'multiOptionalFactorEnroll' => false,
    ],
], JSON_UNESCAPED_SLASHES);

if ($payload === false) {
    fwrite(STDERR, "Nie udało się zbudować payload JSON.\n");
    exit(1);
}

$authCh = curl_init($baseUrl . '/api/v1/authn');
if ($authCh === false) {
    fwrite(STDERR, "Nie udało się zainicjalizować połączenia cURL dla authn.\n");
    exit(1);
}

curl_setopt_array($authCh, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => $payload,
]);

$authResponse = curl_exec($authCh);
if ($authResponse === false) {
    fwrite(STDERR, 'Błąd cURL (authn): ' . curl_error($authCh) . "\n");
    curl_close($authCh);
    exit(1);
}

$authHttpCode = curl_getinfo($authCh, CURLINFO_RESPONSE_CODE);
curl_close($authCh);

$authJson = json_decode($authResponse, true);
if (!is_array($authJson)) {
    fwrite(STDERR, "Niepoprawna odpowiedź JSON z /api/v1/authn.\n");
    fwrite(STDERR, $authResponse . "\n");
    exit(1);
}

$sessionToken = $authJson['sessionToken'] ?? '';
$status = $authJson['status'] ?? 'unknown';

if ($authHttpCode >= 400 || $sessionToken === '') {
    fwrite(STDERR, "Nie udało się pobrać sessionToken. HTTP: {$authHttpCode}, status: {$status}\n");
    fwrite(STDERR, $authResponse . "\n");
    exit(1);
}

$separator = str_contains($authorizeUrl, '?') ? '&' : '?';
$finalUrl = $authorizeUrl . $separator . 'sessionToken=' . rawurlencode((string) $sessionToken);

$pageCh = curl_init($finalUrl);
if ($pageCh === false) {
    fwrite(STDERR, "Nie udało się zainicjalizować połączenia cURL dla pobrania strony.\n");
    exit(1);
}

curl_setopt_array($pageCh, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
]);

$pageHtml = curl_exec($pageCh);
if ($pageHtml === false) {
    fwrite(STDERR, 'Błąd cURL (pobieranie strony): ' . curl_error($pageCh) . "\n");
    curl_close($pageCh);
    exit(1);
}

$pageHttpCode = curl_getinfo($pageCh, CURLINFO_RESPONSE_CODE);
curl_close($pageCh);

if ($pageHttpCode >= 400) {
    fwrite(STDERR, "Błąd HTTP przy pobieraniu strony: {$pageHttpCode}\n");
    exit(1);
}

if (file_put_contents('okta_page.html', $pageHtml) === false) {
    fwrite(STDERR, "Nie udało się zapisać pliku okta_page.html\n");
    exit(1);
}

echo "Zapisano treść strony do: okta_page.html\n";
