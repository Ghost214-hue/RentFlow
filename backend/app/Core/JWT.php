<?php
/**
 * Simple JWT Implementation
 */
namespace App\Core;

class JWT
{
    private static function getSecret(): string
    {
        $config = require __DIR__ . '/../../config/jwt.php';
        return $config['secret_key'];
    }

    private static function getExpiry(): int
    {
        $config = require __DIR__ . '/../../config/jwt.php';
        return $config['expiry_seconds'];
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Generate a JWT token for a given payload
     */
    public static function encode(array $payload): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + self::getExpiry();

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "{$headerEncoded}.{$payloadEncoded}", self::getSecret(), true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "{$headerEncoded}.{$payloadEncoded}.{$signatureEncoded}";
    }

    /**
     * Decode and verify a JWT token
     */
    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            error_log('[JWT DECODE] Invalid token format - expected 3 parts, got ' . count($parts));
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // Verify signature
        $signature = self::base64UrlDecode($signatureEncoded);
        $secret = self::getSecret();
        $expectedSignature = hash_hmac('sha256', "{$headerEncoded}.{$payloadEncoded}", $secret, true);

        if (!hash_equals($expectedSignature, $signature)) {
            error_log('[JWT DECODE] Signature mismatch - Secret Length: ' . strlen($secret) . ' bytes');
            error_log('[JWT DECODE] Expected signature: ' . bin2hex(substr($expectedSignature, 0, 16)) . '...');
            error_log('[JWT DECODE] Actual signature: ' . bin2hex(substr($signature, 0, 16)) . '...');
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        if (!$payload) {
            error_log('[JWT DECODE] Failed to decode payload JSON');
            return null;
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            error_log('[JWT DECODE] Token expired at ' . date('Y-m-d H:i:s', $payload['exp']) . ', current time: ' . date('Y-m-d H:i:s'));
            return null;
        }

        error_log('[JWT DECODE] Success - User: ' . ($payload['email'] ?? 'unknown') . ', Role: ' . ($payload['role'] ?? 'unknown'));
        return $payload;
    }
}