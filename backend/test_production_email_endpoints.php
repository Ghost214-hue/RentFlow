<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=UTF-8');
}

function out(string $message = ''): void { echo $message . PHP_EOL; }

function envValue(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

function loadDotEnv(string $path): bool
{
    if (!is_file($path) || !is_readable($path)) return false;

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
    return true;
}

function uniqueEmail(string $email, string $tag): string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $email;
    [$local, $domain] = explode('@', $email, 2);
    return "{$local}+{$tag}@{$domain}";
}

function request(string $method, string $url, ?array $payload = null, ?string $token = null): array
{
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'User-Agent: RentalFlowEmailEndpointSmokeTest/1.0',
    ];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    if ($payload !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $body = curl_exec($ch);
    $error = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = is_string($body) ? json_decode($body, true) : null;

    return [
        'ok' => $error === '' && $code >= 200 && $code < 300,
        'code' => $code,
        'error' => $error,
        'body' => $body,
        'json' => is_array($json) ? $json : null,
    ];
}

function dataGet(?array $data, string $path): mixed
{
    $current = $data;
    foreach (explode('.', $path) as $part) {
        if (!is_array($current) || !array_key_exists($part, $current)) return null;
        $current = $current[$part];
    }
    return $current;
}

function printResult(string $name, array $response, ?string $assertPath = null, mixed $expected = true): bool
{
    $actual = null;
    $assertOk = true;
    if ($assertPath !== null) {
        $actual = dataGet($response['json'], $assertPath);
        $assertOk = $expected === '__positive__' ? (int) $actual > 0 : $actual === $expected;
    }
    $ok = $response['ok'] && $assertOk;

    out(($ok ? '[PASS] ' : '[FAIL] ') . $name);
    out('       HTTP: ' . $response['code']);
    if ($response['error']) out('       cURL: ' . $response['error']);
    if ($assertPath !== null) out('       Assert: ' . $assertPath . ' = ' . json_encode($actual) . ' expected ' . json_encode($expected));
    $message = $response['json']['message'] ?? $response['json']['error'] ?? null;
    if ($message) out('       Message: ' . $message);
    if (!$ok && !$message && $response['body']) out('       Body: ' . substr((string) $response['body'], 0, 500));
    return $ok;
}

function smtpRead($socket): string
{
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (preg_match('/^\d{3} /', $line)) break;
    }
    return $response;
}

function smtpPreflight(): bool
{
    $host = (string) envValue('MAIL_HOST', '');
    $port = (int) envValue('MAIL_PORT', '587');
    $username = (string) envValue('MAIL_USERNAME', '');
    $password = (string) envValue('MAIL_PASSWORD', '');
    $encryption = strtolower((string) envValue('MAIL_ENCRYPTION', 'tls'));

    out('=== SMTP Preflight ===');
    out('Host: ' . ($host ?: '(missing)'));
    out('Port: ' . $port);
    out('Username: ' . ($username ?: '(missing)'));
    out('Password loaded: ' . ($password !== '' ? 'yes, length ' . strlen($password) : 'no'));
    out('Encryption: ' . $encryption);

    if ($host === '' || $username === '' || $password === '') {
        out('[FAIL] SMTP config is incomplete.');
        out();
        return false;
    }

    out('DNS: ' . gethostbyname($host));
    $target = $encryption === 'ssl' ? 'ssl://' . $host : $host;
    $socket = @fsockopen($target, $port, $errno, $errstr, 15);
    if (!$socket) {
        out("[FAIL] SMTP connection failed: {$errstr} ({$errno})");
        out();
        return false;
    }
    stream_set_timeout($socket, 15);

    $greeting = smtpRead($socket);
    out('Greeting: ' . trim($greeting));
    if (substr($greeting, 0, 3) !== '220') {
        fclose($socket);
        out('[FAIL] SMTP server did not return 220 greeting.');
        out();
        return false;
    }

    fwrite($socket, "EHLO rentalflow.co.ke\r\n");
    $ehlo = smtpRead($socket);
    out('EHLO supports STARTTLS: ' . (stripos($ehlo, 'STARTTLS') !== false ? 'yes' : 'no'));
    out('EHLO supports AUTH: ' . (stripos($ehlo, 'AUTH') !== false ? 'yes' : 'no'));

    if ($encryption === 'tls') {
        fwrite($socket, "STARTTLS\r\n");
        $tls = smtpRead($socket);
        out('STARTTLS: ' . trim($tls));
        if (substr($tls, 0, 3) !== '220') {
            fclose($socket);
            out('[FAIL] STARTTLS was rejected.');
            out();
            return false;
        }
        if (@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
            fclose($socket);
            out('[FAIL] PHP could not enable TLS on the SMTP socket.');
            out();
            return false;
        }
        fwrite($socket, "EHLO rentalflow.co.ke\r\n");
        smtpRead($socket);
    }

    fwrite($socket, "AUTH LOGIN\r\n");
    $auth = smtpRead($socket);
    if (substr($auth, 0, 3) !== '334') {
        fclose($socket);
        out('[FAIL] AUTH LOGIN rejected: ' . trim($auth));
        out();
        return false;
    }
    fwrite($socket, base64_encode($username) . "\r\n");
    $userResponse = smtpRead($socket);
    if (substr($userResponse, 0, 3) !== '334') {
        fclose($socket);
        out('[FAIL] SMTP username rejected: ' . trim($userResponse));
        out();
        return false;
    }
    fwrite($socket, base64_encode($password) . "\r\n");
    $passResponse = smtpRead($socket);
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    if (substr($passResponse, 0, 3) !== '235') {
        out('[FAIL] SMTP password/auth rejected: ' . trim($passResponse));
        out();
        return false;
    }

    out('[PASS] SMTP connection and authentication succeeded.');
    out();
    return true;
}

function runCommandWithTimeout(string $command, int $timeoutSeconds): array
{
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) return ['exit_code' => 1, 'output' => 'Failed to start process.'];
    foreach ($pipes as $pipe) stream_set_blocking($pipe, false);
    $output = '';
    $start = time();
    $timedOut = false;
    while (true) {
        $status = proc_get_status($process);
        $output .= stream_get_contents($pipes[1]);
        $output .= stream_get_contents($pipes[2]);
        if (!$status['running']) break;
        if (time() - $start >= $timeoutSeconds) {
            proc_terminate($process);
            $timedOut = true;
            break;
        }
        usleep(100000);
    }
    foreach ($pipes as $pipe) fclose($pipe);
    $exitCode = proc_close($process);
    return ['exit_code' => $timedOut ? 124 : $exitCode, 'output' => $timedOut ? $output . PHP_EOL . "Timed out after {$timeoutSeconds} seconds." : $output];
}

$envCandidates = [
    dirname(__DIR__) . '/.env.production',
    dirname(__DIR__) . '/.env',
    __DIR__ . '/../.env.production',
    __DIR__ . '/../.env',
    getcwd() . '/.env.production',
    getcwd() . '/.env',
];
$loadedEnvFiles = [];
foreach (array_unique($envCandidates) as $envPath) {
    if (loadDotEnv($envPath)) $loadedEnvFiles[] = $envPath;
}

$allowProduction = envValue('RF_EMAIL_TEST_ALLOW_PRODUCTION') === '1';
$baseUrl = rtrim((string) envValue('RF_EMAIL_TEST_BASE_URL', envValue('APP_URL', 'https://rentalflow.co.ke')), '/');
$apiPrefix = '/' . trim((string) envValue('RF_EMAIL_TEST_API_PREFIX', '/api'), '/');
$ownerEmail = (string) envValue('RF_EMAIL_TEST_OWNER_EMAIL', '');
$ownerPassword = (string) envValue('RF_EMAIL_TEST_OWNER_PASSWORD', '');
$recipientEmail = (string) envValue('RF_EMAIL_TEST_RECIPIENT_EMAIL', envValue('MAIL_USERNAME', ''));
$processQueue = envValue('RF_EMAIL_TEST_PROCESS_QUEUE', '1') === '1';

out('=== RentalFlow Production Email Endpoint Smoke Test ===');
out('Base URL: ' . $baseUrl . $apiPrefix);
out('Recipient: ' . ($recipientEmail ?: '(missing)'));
out('Loaded env files: ' . ($loadedEnvFiles ? implode(', ', $loadedEnvFiles) : '(none found)'));
out();

if (!$allowProduction) {
    out('Refusing to run. Set RF_EMAIL_TEST_ALLOW_PRODUCTION=1 because this sends real emails and creates test records.');
    out('Current working directory: ' . getcwd());
    out('Script directory: ' . __DIR__);
    exit(2);
}
foreach (['RF_EMAIL_TEST_OWNER_EMAIL' => $ownerEmail, 'RF_EMAIL_TEST_OWNER_PASSWORD' => $ownerPassword] as $key => $value) {
    if ($value === '') {
        out("Missing required env var: {$key}");
        exit(2);
    }
}
if ($recipientEmail === '' || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    out('Missing or invalid recipient email. Set RF_EMAIL_TEST_RECIPIENT_EMAIL, or define MAIL_USERNAME in .env.production.');
    exit(2);
}

smtpPreflight();

$api = fn(string $path): string => $baseUrl . $apiPrefix . $path;
$tag = 'rfemail' . date('YmdHis');
$passed = 0;
$failed = 0;
$record = function (string $name, array $response, ?string $assertPath = null, mixed $expected = true) use (&$passed, &$failed): array {
    $ok = printResult($name, $response, $assertPath, $expected);
    $ok ? $passed++ : $failed++;
    out();
    return $response;
};

$login = $record('Owner login', request('POST', $api('/auth/login'), ['email' => $ownerEmail, 'password' => $ownerPassword]));
$token = $login['json']['token'] ?? null;
if (!$token) {
    out('Cannot continue without owner auth token.');
    exit(1);
}

$record('Forgot password email', request('POST', $api('/auth/forgot-password'), ['email' => $ownerEmail]), 'email_sent');
$record('Direct communication email', request('POST', $api('/communications'), [
    'type' => 'email',
    'recipient' => 'RentalFlow Email Test',
    'email' => $recipientEmail,
    'subject' => "RentalFlow smoke test communication {$tag}",
    'message' => "This is the direct communication email endpoint smoke test ({$tag}).",
], $token), 'communication.status', 'sent');

$property = $record('Create smoke-test property', request('POST', $api('/properties'), [
    'name' => "Email Test Property {$tag}",
    'address' => 'Production smoke test address',
    'type' => 'Apartment Block',
    'rent' => 1000,
    'payment_method_type' => 'mobile_money',
    'mobile_money_number' => '0700000000',
], $token));
$propertyId = (int) ($property['json']['property']['id'] ?? 0);

$house = $record('Create smoke-test house', request('POST', $api('/houses'), [
    'property_id' => $propertyId,
    'unit' => "EMAIL-{$tag}",
    'type' => 'Studio',
    'rent' => 1000,
], $token));
$houseId = (int) ($house['json']['house']['id'] ?? 0);

$tenantEmail = uniqueEmail($recipientEmail, $tag . '.tenant');
$kinEmail = uniqueEmail($recipientEmail, $tag . '.kin');
$tenant = $record('Tenant welcome email endpoint', request('POST', $api('/tenants'), [
    'name' => "Email Test Tenant {$tag}",
    'email' => $tenantEmail,
    'phone' => '0700000001',
    'id_number' => substr((string) time(), -8),
    'property_id' => $propertyId,
    'house_id' => $houseId,
    'rent' => 1000,
    'next_of_kin_name' => 'Email Test Next Of Kin',
    'next_of_kin_phone' => '0700000002',
    'next_of_kin_email' => $kinEmail,
], $token), 'email_sent');
$tenantId = (int) ($tenant['json']['tenant']['id'] ?? 0);

$caretakerEmail = uniqueEmail($recipientEmail, $tag . '.caretaker');
$record('Caretaker welcome email endpoint', request('POST', $api('/caretakers'), [
    'name' => "Email Test Caretaker {$tag}",
    'email' => $caretakerEmail,
    'phone' => '0700000003',
    'id_number' => 'CT' . substr((string) time(), -8),
    'assigned_properties' => (string) $propertyId,
], $token), 'email_sent');

$record('Payment confirmation email endpoint', request('POST', $api('/payments'), [
    'tenant_id' => $tenantId,
    'amount' => 100,
    'month' => date('Y-m'),
    'type' => 'Rent',
    'method' => 'Smoke Test',
    'date' => date('Y-m-d'),
    'status' => 'completed',
    'description' => "Email smoke test payment {$tag}",
], $token), 'email_sent');

$record('Billing notification email endpoint', request('POST', $api('/bills/generate'), [
    'month' => date('Y-m'),
    'due_date' => date('Y-m-05'),
    'property_id' => $propertyId,
    'water_charges' => [$houseId => 10],
    'elec_charges' => [$houseId => 10],
], $token), 'emails_sent', '__positive__');

$notice = $record('Management notice email endpoint', request('POST', $api('/complaints'), [
    'recipient_type' => 'individual',
    'tenant_id' => $tenantId,
    'title' => "Email smoke test notice {$tag}",
    'category' => 'Notice',
    'priority' => 'low',
    'description' => "This is the management notice endpoint smoke test ({$tag}).",
], $token), 'email_sent');
$complaintId = (int) ($notice['json']['complaint']['id'] ?? 0);

if ($complaintId > 0) {
    $record('Complaint reply email endpoint', request('PUT', $api('/complaints/' . $complaintId), [
        'comments' => "This is the complaint reply endpoint smoke test ({$tag}).",
        'comment_user' => 'Email Smoke Test',
    ], $token), 'email_sent');
} else {
    out('[FAIL] Complaint reply email endpoint');
    out('       Skipped because management notice did not return a complaint id.');
    out();
    $failed++;
}

if ($processQueue) {
    out('Processing queued emails with backend/cron/email_queue.php ...');
    $cron = __DIR__ . '/cron/email_queue.php';
    if (is_file($cron)) {
        $result = runCommandWithTimeout(PHP_BINARY . ' ' . escapeshellarg($cron), 60);
        out($result['exit_code'] === 0 ? '[PASS] Email queue processor ran' : '[FAIL] Email queue processor failed');
        out($result['output']);
        $result['exit_code'] === 0 ? $passed++ : $failed++;
    } else {
        out('[FAIL] Email queue processor missing at ' . $cron);
        $failed++;
    }
    out();
}

out('=== Summary ===');
out("Passed: {$passed}");
out("Failed: {$failed}");
out('Check inbox/spam, email_logs, email_queue, and backend logs for tag: ' . $tag);
exit($failed > 0 ? 1 : 0);
