<?php

declare(strict_types=1);

function base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function generate_pkce_verifier(): string
{
    return base64url_encode(random_bytes(32));
}

function build_pkce_challenge(string $verifier): string
{
    return base64url_encode(hash('sha256', $verifier, true));
}

function http_request(
    string $method,
    string $url,
    array $headers = [],
    ?string $body = null,
    bool $followRedirects = true
): array {
    $ch = curl_init();
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => $followRedirects,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 30,
    ];

    if (!empty($headers)) {
        $options[CURLOPT_HTTPHEADER] = $headers;
    }

    if ($body !== null) {
        $options[CURLOPT_POSTFIELDS] = $body;
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

function json_request(string $method, string $url, array $payload, array $headers = []): array
{
    $headers[] = 'Content-Type: application/json';
    $response = http_request($method, $url, $headers, json_encode($payload));
    $data = json_decode($response['body'], true);

    if (!is_array($data)) {
        throw new RuntimeException('Invalid JSON response from ' . $url);
    }

    return $data;
}

function parse_code_from_url(string $url): ?string
{
    $parts = parse_url($url);
    if (!isset($parts['query'])) {
        return null;
    }
    parse_str($parts['query'], $query);
    return $query['code'] ?? null;
}

$clientId = getenv('OKTA_CLIENT_ID') ?: 'okta.2b1959c8-bcc0-56eb-a589-cfcfb7422f26';
$redirectUri = getenv('OKTA_REDIRECT_URI') ?: 'https://arcelik.okta-emea.com/enduser/callback';
$scope = getenv('OKTA_SCOPE') ?: 'openid profile email okta.users.read.self okta.users.manage.self okta.internal.enduser.read okta.internal.enduser.manage okta.enduser.dashboard.read okta.enduser.dashboard.manage okta.myAccount.sessions.manage okta.internal.navigation.enduser.read';
$oktaDomain = getenv('OKTA_DOMAIN') ?: 'https://arcelik.okta-emea.com';
$username = getenv('OKTA_USERNAME');
$password = getenv('OKTA_PASSWORD');

if (!$username || !$password) {
    echo "Missing OKTA_USERNAME or OKTA_PASSWORD environment variables.\n";
    exit(1);
}

$options = getopt('', ['task-id::', 'offset::', 'from::', 'to::', 'include-archived::']);
$taskId = $options['task-id'] ?? '27790';
$offset = $options['offset'] ?? '0';
$fromDate = $options['from'] ?? '2025-11-14';
$toDate = $options['to'] ?? '2025-11-21';
$includeArchived = $options['include-archived'] ?? 'false';

$verifier = generate_pkce_verifier();
$challenge = build_pkce_challenge($verifier);
$state = base64url_encode(random_bytes(16));
$nonce = base64url_encode(random_bytes(16));

$authnUrl = $oktaDomain . '/api/v1/authn';
$authnResponse = json_request('POST', $authnUrl, [
    'username' => $username,
    'password' => $password,
]);

if (($authnResponse['status'] ?? '') !== 'SUCCESS') {
    $status = $authnResponse['status'] ?? 'UNKNOWN';
    echo "Okta authentication failed with status: {$status}.\n";
    if (isset($authnResponse['_embedded'])) {
        echo "Additional details: " . json_encode($authnResponse['_embedded']) . "\n";
    }
    exit(1);
}

$sessionToken = $authnResponse['sessionToken'] ?? null;
if (!$sessionToken) {
    echo "Missing sessionToken from Okta response.\n";
    exit(1);
}

$authorizeUrl = sprintf(
    '%s/oauth2/v1/authorize?client_id=%s&code_challenge=%s&code_challenge_method=S256&nonce=%s&redirect_uri=%s&response_type=code&state=%s&scope=%s&sessionToken=%s',
    $oktaDomain,
    rawurlencode($clientId),
    rawurlencode($challenge),
    rawurlencode($nonce),
    rawurlencode($redirectUri),
    rawurlencode($state),
    rawurlencode($scope),
    rawurlencode($sessionToken)
);

$authorizeResponse = http_request('GET', $authorizeUrl, [], null, true);
$code = parse_code_from_url($authorizeResponse['effective_url']);

if (!$code) {
    echo "Authorization code not found in redirect URL.\n";
    exit(1);
}

$tokenUrl = $oktaDomain . '/oauth2/v1/token';
$tokenBody = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'code_verifier' => $verifier,
    'code' => $code,
]);

$tokenResponse = http_request('POST', $tokenUrl, ['Content-Type: application/x-www-form-urlencoded'], $tokenBody, false);
$tokenData = json_decode($tokenResponse['body'], true);

if (!is_array($tokenData) || empty($tokenData['access_token'])) {
    echo "Failed to retrieve access token.\n";
    echo $tokenResponse['body'] . "\n";
    exit(1);
}

$accessToken = $tokenData['access_token'];
$apiUrl = sprintf(
    'https://sirius-api.beko.com/Api/Technician/GetTasksDataDetail/%s/%s/%s/%s/%s/null',
    rawurlencode($taskId),
    rawurlencode($offset),
    rawurlencode($fromDate),
    rawurlencode($toDate),
    rawurlencode($includeArchived)
);

$apiResponse = http_request('GET', $apiUrl, [
    'Authorization: Bearer ' . $accessToken,
    'Accept: application/json',
], null, false);

if ($apiResponse['status'] < 200 || $apiResponse['status'] >= 300) {
    echo "API request failed with status {$apiResponse['status']}.\n";
    echo $apiResponse['body'] . "\n";
    exit(1);
}

echo $apiResponse['body'];
