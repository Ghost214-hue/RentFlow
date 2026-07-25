<?php
/**
 * Production Email Endpoint Tester
 * Tests actual API endpoints that trigger email sending
 */

echo "<pre>";
echo "=== RentalFlow Email Endpoint Tester ===\n\n";

// Configuration
$baseUrl = "https://rentalflow.co.ke";

// Test password reset (no auth required)
echo "TEST 1: Password Reset Request (no auth required)\n";
echo str_repeat('-', 50) . "\n";

$url = $baseUrl . '/api/auth/forgot-password';
$data = ['email' => 'karenjuduncan750@gmail.com'];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "URL: {$url}\n";
echo "HTTP Code: {$httpCode}\n";

if ($error) {
    echo "cURL Error: {$error}\n";
} else {
    echo "Response: {$response}\n";
    
    // Parse response
    $result = json_decode($response, true);
    if ($result) {
        echo "\nParsed Response:\n";
        print_r($result);
    }
}
echo "\n";

echo "=== Test Complete ===\n";
echo "\nIf you see 'email_sent: true', check your inbox!\n";
echo "If you see an error, check the error log at:\n";
echo "/home/cufxccec/public_html/backend/logs/php_errors.log\n";

echo "</pre>";