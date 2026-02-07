<?php

declare(strict_types=1);

function resolve_chromium_binary(): string
{
    $fromEnv = getenv('CHROMIUM_BIN');
    if ($fromEnv) {
        return $fromEnv;
    }

    $candidates = ['chromium', 'chromium-browser', 'google-chrome', 'google-chrome-stable'];
    foreach ($candidates as $candidate) {
        $path = trim((string) shell_exec('command -v ' . escapeshellarg($candidate) . ' 2>/dev/null'));
        if ($path !== '') {
            return $candidate;
        }
    }

    throw new RuntimeException('Chromium binary not found. Set CHROMIUM_BIN to the executable path.');
}

function fetch_page_with_chromium(string $url, string $userAgent, string $chromiumBin): string
{
    $command = sprintf(
        '%s --headless=new --disable-gpu --no-sandbox --user-agent=%s --dump-dom %s',
        escapeshellcmd($chromiumBin),
        escapeshellarg($userAgent),
        escapeshellarg($url)
    );

    $descriptors = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($command, $descriptors, $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Failed to start Chromium process.');
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);
    if ($exitCode !== 0) {
        throw new RuntimeException('Chromium failed with code ' . $exitCode . ': ' . trim($stderr));
    }

    return $stdout;
}

$startUrl = getenv('OKTA_AUTHORIZE_URL') ?: 'https://arcelik.okta-emea.com/oauth2/aus35952jwgf7NWvK0i7/v1/authorize?client_id=0oa6se8rteQZlhlY40i7&code_challenge=h3XYiKGPr5NMciaGAlaMvcxGYP5vEVWTmzm40mJH-nU&code_challenge_method=S256&nonce=jLkVI94EIBZFe8oWSxHOOcFCA8dx798YadUMccvZ3H33vzBpzlaGWM1IHrQTrQxB&redirect_uri=https%3A%2F%2Fsirius-partner.beko.com%2Fimplicit%2Fcallback&response_type=code&state=8SvQftecRhZ7JEZ8ZAb1avzVMvJaAa9t7UR4VIN7fo99MagQ9bJp0ZIdn3ClJ5vv&scope=openid%20email%20profile';
$userAgent = getenv('OKTA_USER_AGENT') ?: 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
$chromiumBin = resolve_chromium_binary();

$pageHtml = fetch_page_with_chromium($startUrl, $userAgent, $chromiumBin);
echo $pageHtml;
