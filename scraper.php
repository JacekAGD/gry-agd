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

$authorizeUrl = getenv('OKTA_AUTHORIZE_URL') ?: 'https://arcelik.okta-emea.com/oauth2/v1/authorize?client_id=okta.2b1959c8-bcc0-56eb-a589-cfcfb7422f26&code_challenge=9IlGS3_z4zBobCUX8CJVrCAqwBC1Jem_zgd1O_Q6i5M&code_challenge_method=S256&nonce=FohnrB183nCCvrqAIfjvqnGeQq0vU3lg0SdZjr6qoTsrVlOIkWd9zqxULzqsH8Wn&redirect_uri=https%3A%2F%2Farcelik.okta-emea.com%2Fenduser%2Fcallback&response_type=code&state=UjRLF40ysGs5CvPziZCzk8OktoAUNILDchaBDxBHE1wwOt2nbPRdlGVBI9ZRb8da&scope=openid%20profile%20email%20okta.users.read.self%20okta.users.manage.self%20okta.internal.enduser.read%20okta.internal.enduser.manage%20okta.enduser.dashboard.read%20okta.enduser.dashboard.manage%20okta.myAccount.sessions.manage%20okta.internal.navigation.enduser.read';
$bookmarkUrl = 'https://arcelik.okta-emea.com/home/bookmark/0oagda9obfeM9qqGs0i7/2557';
$userAgent = getenv('OKTA_USER_AGENT') ?: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
$cookieJar = sys_get_temp_dir() . '/okta_cookie_' . bin2hex(random_bytes(8)) . '.txt';

$authorizeResponse = http_request('GET', $authorizeUrl, [
    'User-Agent: ' . $userAgent,
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
], null, true, $cookieJar);

if (strpos($authorizeResponse['body'], $bookmarkUrl) !== false) {
    $bookmarkResponse = http_request('GET', $bookmarkUrl, [
        'User-Agent: ' . $userAgent,
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    ], null, true, $cookieJar);
    output_response($bookmarkResponse, $bookmarkUrl, 'GET');
    exit(0);
}

output_response($authorizeResponse, $authorizeUrl, 'GET');
