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

$origin = 'https://sirius-partner.beko.com';
$referer = 'https://sirius-partner.beko.com/';
$language = 'pl-PL';
$ipAddress = '0.0.0.0';

$cookieFile = tempnam(sys_get_temp_dir(), 'okta_cookie_');
if ($cookieFile === false) {
    echo "Nie udało się utworzyć pliku cookies.\n";
    exit(1);
}

function request(string $url, string $cookieFile, string $method = 'GET', ?string $body = null, array $headers = [], bool $follow = true): array
{
    $responseHeaders = [];

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
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HEADERFUNCTION => static function ($ch, $headerLine) use (&$responseHeaders) {
            $trimmed = trim($headerLine);
            if ($trimmed !== '') {
                $responseHeaders[] = $trimmed;
            }
            return strlen($headerLine);
        },
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

    return ['httpCode' => $httpCode, 'body' => $response, 'url' => $effectiveUrl, 'headers' => $responseHeaders];
}

function extractBearerCandidate(array $responses): string
{
    $patterns = [
        '/"access_token"\s*:\s*"([^"]+)"/i',
        '/"id_token"\s*:\s*"([^"]+)"/i',
        '/access_token=([^&"\s]+)/i',
        '/id_token=([^&"\s]+)/i',
    ];

    foreach ($responses as $response) {
        $url = $response['url'] ?? '';
        $body = $response['body'] ?? '';
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m) === 1) {
                return rawurldecode((string) $m[1]);
            }
            if (preg_match($pattern, $body, $m) === 1) {
                return rawurldecode((string) $m[1]);
            }
        }
    }

    return '';
}

function extractHeaderValueCandidate(string $name, array $responses): string
{
    $name = preg_quote($name, '/');
    $patterns = [
        '/"' . $name . '"\s*:\s*"([^"]+)"/i',
        '/\b' . $name . '\b\s*[:=]\s*"?([^",\s}]+)/i',
        '/\b' . $name . '=([^;\s]+)/i',
    ];

    foreach ($responses as $response) {
        foreach ([$response['url'] ?? '', $response['body'] ?? ''] as $source) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $source, $m) === 1) {
                    return rawurldecode((string) $m[1]);
                }
            }
        }

        foreach (($response['headers'] ?? []) as $headerLine) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $headerLine, $m) === 1) {
                    return rawurldecode((string) $m[1]);
                }
            }
        }
    }

    return '';
}

try {
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

    $auth = request($baseUrl . '/api/v1/authn', $cookieFile, 'POST', $payload, ['Accept: application/json', 'Content-Type: application/json'], false);
    $authJson = json_decode($auth['body'], true);
    if (!is_array($authJson)) {
        throw new RuntimeException('Niepoprawna odpowiedź JSON z /api/v1/authn.');
    }

    $sessionToken = $authJson['sessionToken'] ?? '';
    if ($auth['httpCode'] >= 400 || $sessionToken === '') {
        $status = $authJson['status'] ?? 'unknown';
        throw new RuntimeException("Nie udało się pobrać sessionToken. HTTP: {$auth['httpCode']}, status: {$status}");
    }

    $separator = str_contains($authorizeUrl, '?') ? '&' : '?';
    $authorizeWithToken = $authorizeUrl . $separator . 'sessionToken=' . rawurlencode((string) $sessionToken);

    $authorizeResponse = request($authorizeWithToken, $cookieFile, 'GET');
    if ($authorizeResponse['httpCode'] >= 400) {
        throw new RuntimeException('Błąd wejścia na authorize URL. HTTP: ' . $authorizeResponse['httpCode']);
    }

    $appResponse = request($appUrl, $cookieFile, 'GET');
    if ($appResponse['httpCode'] >= 400) {
        throw new RuntimeException('Błąd wejścia na aplikację Sirius. HTTP: ' . $appResponse['httpCode']);
    }

    $accessControlRequestHeaders = 'auth-userid,authorization,ipaddress,language,max-age,sessiontoken,sessiontokenws,x-frame-options,x-xss-protection';
    $preflightHeaders = [
        'Accept: */*',
        'Origin: ' . $origin,
        'Referer: ' . $referer,
        'Sec-Fetch-Dest: empty',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Site: same-site',
        'Access-Control-Request-Method: GET',
        'Access-Control-Request-Headers: ' . $accessControlRequestHeaders,
    ];
    $preflight = request($apiUrl, $cookieFile, 'OPTIONS', null, $preflightHeaders, false);
    if (!in_array($preflight['httpCode'], [200, 204], true)) {
        throw new RuntimeException('Preflight OPTIONS nie przeszedł. HTTP: ' . $preflight['httpCode']);
    }

    $token = extractBearerCandidate([$authorizeResponse, $appResponse]);

    // W praktyce Sirius zwykle wymaga tokenów specyficznych dla aplikacji,
    // nie surowego sessionToken z Okta. Próbujemy je wydobyć z odpowiedzi appki.
    $sessionTokenWs = extractHeaderValueCandidate('sessiontokenws', [$appResponse, $authorizeResponse]);
    $sessionTokenApi = extractHeaderValueCandidate('sessiontoken', [$appResponse, $authorizeResponse]);
    $authUserId = extractHeaderValueCandidate('auth-userid', [$appResponse, $authorizeResponse]);

    if ($sessionTokenWs === '') {
        $sessionTokenWs = $sessionToken;
    }
    if ($sessionTokenApi === '') {
        $sessionTokenApi = $sessionToken;
    }
    if ($authUserId === '') {
        $authUserId = $login;
    }

    $apiHeaders = [
        'Accept: application/json, text/plain, */*',
        'Origin: ' . $origin,
        'Referer: ' . $referer,
        'Language: ' . $language,
        'IpAddress: ' . $ipAddress,
        'Max-Age: 0',
        'X-Frame-Options: SAMEORIGIN',
        'X-XSS-Protection: 1; mode=block',
        'Auth-UserId: ' . $authUserId,
        'SessionToken: ' . $sessionTokenApi,
        'SessionTokenWs: ' . $sessionTokenWs,
    ];
    if ($token !== '') {
        $apiHeaders[] = 'Authorization: Bearer ' . $token;
    }

    $apiResponse = request($apiUrl, $cookieFile, 'GET', null, $apiHeaders, false);
    if ($apiResponse['httpCode'] === 401 && $token === '') {
        $token = extractBearerCandidate([$authorizeResponse, $appResponse, $apiResponse]);
        if ($token !== '') {
            $apiHeaders[] = 'Authorization: Bearer ' . $token;
            $apiResponse = request($apiUrl, $cookieFile, 'GET', null, $apiHeaders, false);
        }
    }

    if ($apiResponse['httpCode'] >= 400) {
        throw new RuntimeException(
            'Błąd pobierania API. HTTP: ' . $apiResponse['httpCode']
            . '. Final app URL: ' . $appResponse['url']
            . '. Użyte Auth-UserId: ' . $authUserId
            . '. SessionTokenWs length: ' . strlen($sessionTokenWs)
        );
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
