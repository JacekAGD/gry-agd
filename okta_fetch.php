#!/usr/bin/env php
<?php
declare(strict_types=1);

// Uzupełnij danymi logowania.
$login = 'YOUR_LOGIN';
$pass = 'YOUR_PASSWORD';

if ($login === 'YOUR_LOGIN' || $pass === 'YOUR_PASSWORD') {
    echo "Uzupełnij zmienne login i pass w pliku okta_fetch.php\n";
    exit(1);
}

$baseUrl = 'https://arcelik.okta-emea.com';
$authorizeUrl = 'https://arcelik.okta-emea.com/oauth2/v1/authorize?client_id=okta.2b1959c8-bcc0-56eb-a589-cfcfb7422f26&code_challenge=PgpvH9uwEFyESRTVT_Z-F_kNXWsSBz8mdmP0hyk6RGY&code_challenge_method=S256&nonce=57cRjuEV5z48yL08QoutsjdMrHTuHUQxtFptaW9SAnH3746EkCiE2o275MjsNIcl&redirect_uri=https%3A%2F%2Farcelik.okta-emea.com%2Fenduser%2Fcallback&response_type=code&state=CTMr1LoHzERaECK8izTFb0YvFWvsBfGNWSsajxFWuyXAaNYfKsTxfBhDQ26RE7vG&scope=openid%20profile%20email%20okta.users.read.self%20okta.users.manage.self%20okta.internal.enduser.read%20okta.internal.enduser.manage%20okta.enduser.dashboard.read%20okta.enduser.dashboard.manage%20okta.myAccount.sessions.manage%20okta.internal.navigation.enduser.read';
$appUrl = 'https://arcelik.okta-emea.com/home/bookmark/0oagda9obfeM9qqGs0i7/2557';
$apiUrl = 'https://sirius-api.beko.com/Api/Technician/GetTasksDataDetail/27790/0/2025-11-14/2025-11-21/false/null';
$outputFile = 'sirius_tasks_data.json';

$cookieFile = tempnam(sys_get_temp_dir(), 'okta_cookie_');
if ($cookieFile === false) {
    echo "Nie udało się utworzyć pliku cookies.\n";
    exit(1);
}

function request(
    string $url,
    string $cookieFile,
    string $method = 'GET',
    ?string $body = null,
    array $headers = [],
    bool $follow = true
): array {
    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('Nie udało się zainicjalizować cURL.');
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; okta-fetch-php/1.0)',
        CURLOPT_CUSTOMREQUEST => $method,
    ]);

    if ($headers !== []) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Błąd cURL: ' . $error);
    }

    $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    return [
        'httpCode' => $httpCode,
        'body' => $response,
        'url' => $effectiveUrl,
    ];
}

try {
    // 1) Authn -> sessionToken
    $payload = json_encode([
        'username' => $login,
        'password' => $pass,
        'options' => [
            'warnBeforePasswordExpired' => true,
            'multiOptionalFactorEnroll' => false,
        ],
    ], JSON_UNESCAPED_SLASHES);

    if ($payload === false) {
        throw new RuntimeException('Nie udało się zbudować payload JSON.');
    }

    $auth = request(
        $baseUrl . '/api/v1/authn',
        $cookieFile,
        'POST',
        $payload,
        ['Accept: application/json', 'Content-Type: application/json'],
        false
    );

    $authJson = json_decode($auth['body'], true);
    if (!is_array($authJson)) {
        throw new RuntimeException('Niepoprawna odpowiedź JSON z /api/v1/authn.');
    }

    $sessionToken = $authJson['sessionToken'] ?? '';
    if ($auth['httpCode'] >= 400 || $sessionToken === '') {
        $status = $authJson['status'] ?? 'unknown';
        throw new RuntimeException("Nie udało się pobrać sessionToken. HTTP: {$auth['httpCode']}, status: {$status}");
    }

    // 2) Wejście przez authorize z sessionToken (ustanawia sesję Okta)
    $separator = str_contains($authorizeUrl, '?') ? '&' : '?';
    $authorizeWithToken = $authorizeUrl . $separator . 'sessionToken=' . rawurlencode((string) $sessionToken);

    $authorizeResponse = request($authorizeWithToken, $cookieFile, 'GET');
    if ($authorizeResponse['httpCode'] >= 400) {
        throw new RuntimeException('Błąd wejścia na authorize URL. HTTP: ' . $authorizeResponse['httpCode']);
    }

    // 3) Wejście na kafelek aplikacji Sirius
    $appResponse = request($appUrl, $cookieFile, 'GET');
    if ($appResponse['httpCode'] >= 400) {
        throw new RuntimeException('Błąd wejścia na aplikację Sirius. HTTP: ' . $appResponse['httpCode']);
    }

    // 4) Pobranie API już na aktywnej sesji/cookies
    $apiResponse = request($apiUrl, $cookieFile, 'GET');
    if ($apiResponse['httpCode'] >= 400) {
        throw new RuntimeException('Błąd pobierania API. HTTP: ' . $apiResponse['httpCode']);
    }

    if (file_put_contents($outputFile, $apiResponse['body']) === false) {
        throw new RuntimeException('Nie udało się zapisać pliku wyjściowego.');
    }

    echo "Zapisano dane API do: {$outputFile}\n";
} catch (Throwable $e) {
    echo 'Błąd podczas automatyzacji logowania/pobierania danych: ' . $e->getMessage() . "\n";
    @unlink($cookieFile);
    exit(1);
}

@unlink($cookieFile);
