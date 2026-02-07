<?php

declare(strict_types=1);

function http_request(
    string $method,
    string $url,
    array $headers = [],
    ?string $body = null,
    bool $followRedirects = true,
    ?string $cookieJar = null
): array {
    $ch = curl_init();
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => $followRedirects,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
    ];

    if (!empty($headers)) {
        $options[CURLOPT_HTTPHEADER] = $headers;
    }

    if ($body !== null) {
        $options[CURLOPT_POSTFIELDS] = $body;
    }

    if ($cookieJar !== null) {
        $options[CURLOPT_COOKIEJAR] = $cookieJar;
        $options[CURLOPT_COOKIEFILE] = $cookieJar;
    }

    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Curl error: ' . $error);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $headerText = substr($response, 0, $headerSize);
    $bodyText = substr($response, $headerSize);

    return [
        'status' => $status,
        'headers' => $headerText,
        'body' => $bodyText,
        'effective_url' => $effectiveUrl,
    ];
}

function output_response(array $response, string $requestUrl, string $method): void
{
    echo "==== HTTP RESPONSE ====" . "\n";
    echo "REQUEST: " . strtoupper($method) . " " . $requestUrl . "\n";
    echo "STATUS: {$response['status']}\n";
    echo "EFFECTIVE URL: {$response['effective_url']}\n";
    echo "---- HEADERS ----\n";
    echo trim($response['headers']) . "\n";
    echo "---- BODY ----\n";
    echo $response['body'] . "\n";
    echo "==== END HTTP RESPONSE ====" . "\n";
}

$startUrl = getenv('OKTA_AUTHORIZE_URL') ?: 'https://arcelik.okta-emea.com/oauth2/aus35952jwgf7NWvK0i7/v1/authorize?client_id=0oa6se8rteQZlhlY40i7&code_challenge=h3XYiKGPr5NMciaGAlaMvcxGYP5vEVWTmzm40mJH-nU&code_challenge_method=S256&nonce=jLkVI94EIBZFe8oWSxHOOcFCA8dx798YadUMccvZ3H33vzBpzlaGWM1IHrQTrQxB&redirect_uri=https%3A%2F%2Fsirius-partner.beko.com%2Fimplicit%2Fcallback&response_type=code&state=8SvQftecRhZ7JEZ8ZAb1avzVMvJaAa9t7UR4VIN7fo99MagQ9bJp0ZIdn3ClJ5vv&scope=openid%20email%20profile';
$userAgent = getenv('OKTA_USER_AGENT') ?: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
$cookieJar = sys_get_temp_dir() . '/okta_cookie_' . bin2hex(random_bytes(8)) . '.txt';

$finalResponse = http_request('GET', $startUrl, [
    'User-Agent: ' . $userAgent,
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
], null, true, $cookieJar);

output_response($finalResponse, $startUrl, 'GET');
