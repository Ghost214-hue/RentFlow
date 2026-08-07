<?php
namespace App\Core;

class IdEncoder
{
    private const CIPHER = 'AES-256-CBC';
    private const IV_LENGTH = 16;

    private static function getKey(): string
    {
        $config = require __DIR__ . '/../../config/jwt.php';
        return hash('sha256', $config['secret_key'], true);
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string|false
    {
        $padding = 4 - (strlen($data) % 4);
        if ($padding !== 4) {
            $data .= str_repeat('=', $padding);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function encode(int $id): string
    {
        $key = self::getKey();
        $iv = random_bytes(self::IV_LENGTH);
        $cipherText = openssl_encrypt((string) $id, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        if ($cipherText === false) {
            return '';
        }
        return self::base64UrlEncode($iv . $cipherText);
    }

    public static function decode(string $token): ?int
    {
        $raw = self::base64UrlDecode($token);
        if ($raw === false || strlen($raw) < self::IV_LENGTH) {
            return null;
        }

        $iv = substr($raw, 0, self::IV_LENGTH);
        $cipherText = substr($raw, self::IV_LENGTH);
        $key = self::getKey();
        $decrypted = openssl_decrypt($cipherText, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) {
            return null;
        }

        $id = filter_var($decrypted, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false ? null : $id;
    }
}
