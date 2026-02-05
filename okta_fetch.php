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

$authorizeUrl = 'https://arcelik.okta-emea.com/oauth2/v1/authorize?client_id=okta.2b1959c8-bcc0-56eb-a589-cfcfb7422f26&code_challenge=PgpvH9uwEFyESRTVT_Z-F_kNXWsSBz8mdmP0hyk6RGY&code_challenge_method=S256&nonce=57cRjuEV5z48yL08QoutsjdMrHTuHUQxtFptaW9SAnH3746EkCiE2o275MjsNIcl&redirect_uri=https%3A%2F%2Farcelik.okta-emea.com%2Fenduser%2Fcallback&response_type=code&state=CTMr1LoHzERaECK8izTFb0YvFWvsBfGNWSsajxFWuyXAaNYfKsTxfBhDQ26RE7vG&scope=openid%20profile%20email%20okta.users.read.self%20okta.users.manage.self%20okta.internal.enduser.read%20okta.internal.enduser.manage%20okta.enduser.dashboard.read%20okta.enduser.dashboard.manage%20okta.myAccount.sessions.manage%20okta.internal.navigation.enduser.read';
$apiUrl = 'https://sirius-api.beko.com/Api/Technician/GetTasksDataDetail/27790/0/2025-11-14/2025-11-21/false/null';
$outputFile = 'sirius_tasks_data.json';

$nodeScript = <<<'JS'
const fs = require('fs');

(async () => {
  let playwright;
  try {
    playwright = require('playwright');
  } catch (_e) {
    throw new Error('Brak modułu "playwright". Uruchom: npm install playwright && npx playwright install chromium');
  }

  const login = process.env.OKTA_LOGIN;
  const pass = process.env.OKTA_PASS;
  const authorizeUrl = process.env.OKTA_AUTHORIZE_URL;
  const apiUrl = process.env.SIRIUS_API_URL;
  const outputFile = process.env.OKTA_OUTPUT_FILE;

  const browser = await playwright.chromium.launch({ headless: true });
  const context = await browser.newContext({
    locale: 'pl-PL',
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',
  });

  const page = await context.newPage();

  await page.goto(authorizeUrl, { waitUntil: 'domcontentloaded', timeout: 120000 });
  await page.locator('input[name="identifier"]').first().fill(login);
  await page.locator('input[name="credentials.passcode"]').first().fill(pass);

  const submitCandidates = [
    'input[type="submit"]',
    'button[type="submit"]',
    '#okta-signin-submit',
    'input[data-type="save"]',
    'button[data-type="save"]',
  ];

  let submitted = false;
  for (const selector of submitCandidates) {
    const count = await page.locator(selector).count();
    if (count > 0) {
      await page.locator(selector).first().click();
      submitted = true;
      break;
    }
  }

  if (!submitted) {
    throw new Error('Nie znaleziono przycisku logowania.');
  }

  await page.waitForLoadState('domcontentloaded', { timeout: 90000 });

  const appSelectors = [
    'a[data-se="app-card"][href="https://arcelik.okta-emea.com/home/bookmark/0oagda9obfeM9qqGs0i7/2557"]',
    'a[data-se="app-card"]:has-text("Sirius Partner - Beko")',
    'a[aria-label="uruchom aplikację Sirius Partner - Beko"]',
  ];

  let appClicked = false;
  for (const selector of appSelectors) {
    const loc = page.locator(selector);
    const count = await loc.count();
    if (count > 0) {
      await loc.first().click();
      appClicked = true;
      break;
    }
  }

  if (!appClicked) {
    throw new Error('Nie znaleziono kafelka aplikacji Sirius Partner - Beko.');
  }

  await page.waitForLoadState('domcontentloaded', { timeout: 120000 });

  // fetch wykonywany w kontekście strony po zalogowaniu - przeglądarka sama dobierze cookies/CORS/preflight.
  const apiResult = await page.evaluate(async ({ apiUrl, login }) => {
    const headers = {
      'accept': 'application/json, text/plain, */*',
      'language': navigator.language || 'pl-PL',
      'auth-userid': login,
      'max-age': '0',
      'x-frame-options': 'SAMEORIGIN',
      'x-xss-protection': '1; mode=block',
    };

    const response = await fetch(apiUrl, {
      method: 'GET',
      mode: 'cors',
      credentials: 'include',
      headers,
    });

    const text = await response.text();
    return {
      status: response.status,
      ok: response.ok,
      body: text,
      url: response.url,
    };
  }, { apiUrl, login });

  if (!apiResult.ok) {
    throw new Error(`Błąd pobierania API. HTTP: ${apiResult.status}. Final URL: ${apiResult.url}`);
  }

  fs.writeFileSync(outputFile, apiResult.body, 'utf8');
  await browser.close();

  console.log(`Zapisano dane API do: ${outputFile}`);
})().catch((err) => {
  console.error(String(err && err.message ? err.message : err));
  process.exit(1);
});
JS;


function resolveNodeBinary(): string
{
    $candidates = [
        getenv('NODE_BIN') ?: '',
        '/usr/bin/node',
        '/usr/local/bin/node',
        '/opt/homebrew/bin/node',
        '/bin/node',
    ];

    $whichNode = @shell_exec('command -v node 2>/dev/null');
    if (is_string($whichNode)) {
        $candidates[] = trim($whichNode);
    }

    foreach (array_unique($candidates) as $candidate) {
        if ($candidate !== '' && @is_executable($candidate)) {
            return $candidate;
        }
    }

    return '';
}

$tmpNode = tempnam(sys_get_temp_dir(), 'okta_node_');
if ($tmpNode === false) {
    echo "Nie udało się utworzyć pliku tymczasowego.\n";
    exit(1);
}

if (file_put_contents($tmpNode, $nodeScript) === false) {
    echo "Nie udało się zapisać skryptu Node.js.\n";
    @unlink($tmpNode);
    exit(1);
}

$nodeBinary = resolveNodeBinary();
if ($nodeBinary === '') {
    echo "Nie znaleziono binarki Node.js. Ustaw NODE_BIN lub zainstaluj node w systemie.\n";
    @unlink($tmpNode);
    exit(1);
}

$cmd = escapeshellarg($nodeBinary) . ' ' . escapeshellarg($tmpNode);
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$env = array_merge($_ENV, [
    'OKTA_LOGIN' => $login,
    'OKTA_PASS' => $pass,
    'OKTA_AUTHORIZE_URL' => $authorizeUrl,
    'SIRIUS_API_URL' => $apiUrl,
    'OKTA_OUTPUT_FILE' => $outputFile,
]);

$process = proc_open($cmd, $descriptors, $pipes, null, $env);
if (!is_resource($process)) {
    echo "Nie udało się uruchomić Node.js.\n";
    @unlink($tmpNode);
    exit(1);
}

fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);

$exitCode = proc_close($process);
@unlink($tmpNode);

if ($stdout !== '') {
    echo $stdout;
}

if ($exitCode !== 0) {
    echo "Błąd podczas automatyzacji logowania/pobierania danych.\n";
    if ($stderr !== '') {
        echo $stderr;
    }
    exit($exitCode);
}
