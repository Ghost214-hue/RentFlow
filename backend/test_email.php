<?php
/**
 * RentalFlow Truehost SMTP Authentication Test
 * Delete after testing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";


// ===============================
// LOAD CONFIG FROM .env.production
// ===============================

$envPath = __DIR__ . '/../.env.production';
echo "ENV PATH: $envPath\n";
echo "ENV EXISTS: " . (file_exists($envPath) ? 'YES' : 'NO') . "\n";
echo "ENV READABLE: " . (is_readable($envPath) ? 'YES' : 'NO') . "\n";
echo "ENV SIZE: " . filesize($envPath) . " bytes\n\n";

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "ENV LINES: " . count($lines) . "\n\n";
    foreach ($lines as $lineNum => $line) {
        $originalLine = $line;
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue;
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        // Remove surrounding quotes if present
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        putenv($key . '=' . $value);
        if ($key === 'MAIL_PASSWORD') {
            echo "FOUND MAIL_PASSWORD at line $lineNum\n";
            echo "  Raw: " . json_encode($originalLine) . "\n";
            echo "  Key: '$key'\n";
            echo "  Value length: " . strlen($value) . "\n";
            echo "  Value bytes: " . json_encode($value) . "\n";
            echo "  Hex: " . bin2hex($value) . "\n\n";
        }
    }
}

// ===============================
// SMTP SETTINGS
// ===============================

$smtpHost = getenv('MAIL_HOST') ?: "mail.rentalflow.co.ke";
$smtpPort = (int) (getenv('MAIL_PORT') ?: 587);

$username = trim(getenv('MAIL_USERNAME') ?: "nonreply@rentalflow.co.ke");
$password = trim(getenv('MAIL_PASSWORD') ?: "");

$fromEmail = trim(getenv('MAIL_FROM_EMAIL') ?: "nonreply@rentalflow.co.ke");
$fromName  = trim(getenv('MAIL_FROM_NAME') ?: "RentalFlow");

$recipient = "karenjuduncan750@gmail.com";

// Show ALL parsed env values for debugging
echo "ALL PARSED ENV VALUES (putenv):\n";
echo "MAIL_HOST=" . getenv('MAIL_HOST') . "\n";
echo "MAIL_PORT=" . getenv('MAIL_PORT') . "\n";
echo "MAIL_USERNAME=" . getenv('MAIL_USERNAME') . "\n";
echo "MAIL_PASSWORD=" . (getenv('MAIL_PASSWORD') ?: 'EMPTY') . "\n";
echo "MAIL_FROM_EMAIL=" . getenv('MAIL_FROM_EMAIL') . "\n\n";

// Debug: Show if password was loaded
echo "\nPASSWORD LOADED: " . (empty($password) ? "NO - USING EMPTY STRING" : "YES") . "\n";
echo "PASSWORD LENGTH: " . strlen($password) . "\n";
echo "PASSWORD (first 3 chars): " . substr($password, 0, 3) . "\n";
echo "PASSWORD (last 3 chars): " . substr($password, -3) . "\n\n";



// ===============================
// SMTP RESPONSE READER
// ===============================

function smtpRead($socket)
{
    $response = "";

    while (($line = fgets($socket, 515)) !== false) {

        $response .= $line;

        if (preg_match('/^\d{3} /', $line)) {
            break;
        }
    }

    return $response;
}



echo "=====================================\n";
echo " RentalFlow SMTP TEST\n";
echo "=====================================\n\n";



// PHP INFO

echo "PHP INFORMATION\n";
echo "----------------\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "SAPI: " . php_sapi_name() . "\n\n";



// CONFIG

echo "SMTP CONFIGURATION\n";
echo "------------------\n";

echo "Host: $smtpHost\n";
echo "Port: $smtpPort\n";
echo "Username: $username\n";
echo "From: $fromEmail\n\n";



// DNS

echo "TEST 1: DNS\n";
echo "-----------\n";

echo gethostbyname($smtpHost);

echo "\n\n";



// CONNECT

echo "TEST 2: CONNECTION\n";
echo "------------------\n";


$socket = fsockopen(
    $smtpHost,
    $smtpPort,
    $errno,
    $errstr,
    15
);


if (!$socket) {

    echo "FAILED\n";
    echo "$errstr ($errno)";
    exit;

}


echo "CONNECTED\n\n";



// GREETING

echo "TEST 3: SERVER GREETING\n";
echo "-----------------------\n";

echo smtpRead($socket);

echo "\n";



// EHLO

echo "TEST 4: EHLO\n";
echo "------------\n";


fwrite(
    $socket,
    "EHLO rentalflow.co.ke\r\n"
);


$response = smtpRead($socket);

echo $response;



if (stripos($response,"STARTTLS") === false) {

    echo "\nSTARTTLS unavailable\n";
    exit;

}


echo "\nSTARTTLS AVAILABLE\n\n";



// STARTTLS

echo "TEST 5: TLS\n";
echo "-----------\n";


fwrite(
    $socket,
    "STARTTLS\r\n"
);


$response = smtpRead($socket);

echo $response;


if (strpos($response,"220") !== 0) {

    echo "STARTTLS FAILED";
    exit;

}



stream_socket_enable_crypto(
    $socket,
    true,
    STREAM_CRYPTO_METHOD_TLS_CLIENT
);


echo "TLS ENABLED\n\n";



// EHLO AGAIN

fwrite(
    $socket,
    "EHLO rentalflow.co.ke\r\n"
);


smtpRead($socket);



// AUTH

echo "TEST 6: LOGIN AUTH\n";
echo "------------------\n";


fwrite(
    $socket,
    "AUTH LOGIN\r\n"
);


echo smtpRead($socket);



fwrite(
    $socket,
    base64_encode($username)."\r\n"
);


echo smtpRead($socket);



fwrite(
    $socket,
    base64_encode($password)."\r\n"
);


$response = smtpRead($socket);


echo $response;



if (strpos($response,"235") !== 0) {

    echo "\n\nAUTHENTICATION FAILED\n";
    exit;

}


echo "\nAUTHENTICATION SUCCESSFUL\n\n";



// SEND EMAIL

echo "TEST 7: SEND EMAIL\n";
echo "-----------------\n";


fwrite(
    $socket,
    "MAIL FROM:<$fromEmail>\r\n"
);

echo smtpRead($socket);



fwrite(
    $socket,
    "RCPT TO:<$recipient>\r\n"
);

echo smtpRead($socket);



fwrite(
    $socket,
    "DATA\r\n"
);

echo smtpRead($socket);



$message =
"From: $fromName <$fromEmail>\r\n".
"To: $recipient\r\n".
"Subject: RentalFlow SMTP Test\r\n".
"Content-Type: text/html; charset=UTF-8\r\n".
"\r\n".
"
<h2>RentalFlow SMTP Working</h2>
<p>This confirms production email is configured correctly.</p>
".
"\r\n.\r\n";


fwrite(
    $socket,
    $message
);


echo smtpRead($socket);



fwrite(
    $socket,
    "QUIT\r\n"
);


fclose($socket);



echo "\n=====================================\n";
echo "TEST COMPLETED\n";
echo "=====================================\n";


echo "</pre>";