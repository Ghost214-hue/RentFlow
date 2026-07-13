<?php
/**
 * Email Configuration Test
 * Diagnoses email delivery issues and suggests fixes
 */

echo "=== Email Configuration Diagnostic ===\n\n";

// Check .env settings
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    echo "ERROR: .env file not found!\n";
    exit(1);
}

$envContent = file_get_contents($envFile);
parse_str(str_replace("\n", "&", $envContent), $envVars);

echo "Current Configuration:\n";
echo "  MAIL_USE_SMTP: " . ($envVars['MAIL_USE_SMTP'] ?? 'not set') . "\n";
echo "  MAIL_HOST: " . ($envVars['MAIL_HOST'] ?? 'not set') . "\n";
echo "  MAIL_PORT: " . ($envVars['MAIL_PORT'] ?? 'not set') . "\n";
echo "  MAIL_USERNAME: " . ($envVars['MAIL_USERNAME'] ?? 'not set') . "\n";
echo "  MAIL_PASSWORD: " . ($envVars['MAIL_PASSWORD'] ?? 'not set') . "\n";
echo "  MAIL_FROM_EMAIL: " . ($envVars['MAIL_FROM_EMAIL'] ?? 'not set') . "\n\n";

// Check DNS resolution
echo "Testing DNS Resolution:\n";
$host = $envVars['MAIL_HOST'] ?? 'smtp.gmail.com';
$port = $envVars['MAIL_PORT'] ?? 587;

$ip = gethostbyname($host);
if ($ip === $host) {
    echo "  ✗ FAILED: Cannot resolve $host\n";
    echo "  This means DNS is not working or blocked\n";
    echo "  Solutions:\n";
    echo "    1. Check internet connection\n";
    echo "    2. Configure DNS in /etc/resolv.conf\n";
    echo "    3. Use IP address instead of hostname (if possible)\n";
} else {
    echo "  ✓ SUCCESS: $host resolves to $ip\n";
}

// Check if port is accessible
echo "\nTesting Port Connectivity:\n";
$connection = @fsockopen($host, $port, $errno, $errstr, 5);
if ($connection) {
    echo "  ✓ SUCCESS: Port $port is open on $host\n";
    fclose($connection);
} else {
    echo "  ✗ FAILED: Cannot connect to $host:$port\n";
    echo "  Error: $errstr (Code: $errno)\n";
    echo "  Solutions:\n";
    echo "    1. Check firewall settings\n";
    echo "    2. Check if ISP blocks port 587\n";
    echo "    3. Try port 465 (SSL) instead\n";
}

// Check PHP mail configuration
echo "\nPHP Mail Configuration:\n";
echo "  sendmail_path: " . ini_get('sendmail_path') . "\n";
echo "  SMTP: " . ini_get('SMTP') . "\n";
echo "  smtp_port: " . ini_get('smtp_port') . "\n";

// Check if mail() function exists
if (function_exists('mail')) {
    echo "  ✓ mail() function is available\n";
} else {
    echo "  ✗ mail() function is NOT available\n";
}

// Test sending
echo "\n" . str_repeat("=", 50) . "\n";
echo "RECOMMENDATIONS:\n\n";

if ($envVars['MAIL_USE_SMTP'] === 'true') {
    if ($ip === $host) {
        echo "1. DNS ISSUE: Cannot resolve SMTP host\n";
        echo "   → Fix DNS or try alternative SMTP host\n";
    } else {
        echo "1. SMTP is configured but connection failed\n";
        echo "   → Check firewall/ISP blocks\n";
        echo "   → Verify SMTP credentials\n";
    }
} else {
    echo "1. Using PHP mail() - needs sendmail configured\n";
    echo "   Solution A: Configure XAMPP sendmail (see below)\n";
    echo "   Solution B: Switch to SMTP with working credentials\n";
    echo "   Solution C: Use MailHog/MailCatcher for development\n";
}

echo "\n=== XAMPP Sendmail Configuration ===\n";
echo "Edit: /opt/lampp/etc/php.ini\n";
echo "Find: sendmail_path =\n";
echo "Set to: sendmail_path = \"/usr/sbin/sendmail -t -i\"\n\n";

echo "Or create /opt/lampp/php/lib/php.ini with:\n";
echo "sendmail_path = /usr/sbin/sendmail -t -i\n\n";

echo "=== Quick Fix for Development ===\n";
echo "Use MailHog (email testing tool):\n";
echo "1. Install: go install github.com/mailhog/MailHog\n";
echo "2. Run: mailhog\n";
echo "3. Set MAIL_HOST=localhost, MAIL_PORT=1025\n";
echo "4. View emails at: http://localhost:8025\n";

exit(0);