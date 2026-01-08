<?php

namespace Firebase\JWT;

/**
 * Stub class untuk Firebase JWT
 * Ini adalah fallback ketika package tidak terinstall
 */
class JWT
{
    /**
     * Encode JWT token
     */
    public static function encode(array $payload, string $key, string $algorithm = 'HS256'): string
    {
        // Simple base64 encoding fallback
        $header = json_encode(['alg' => $algorithm, 'typ' => 'JWT']);
        $payload = json_encode($payload);
        
        $base64UrlHeader = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64UrlPayload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
        
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $key, true);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        
        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    /**
     * Decode JWT token
     */
    public static function decode(string $token, $key, array $allowedAlgos = ['HS256']): object
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \Exception('Invalid JWT format');
        }

        list($header, $payload, $signature) = $parts;

        $payload = json_decode(base64_decode(strtr($payload, '-_', '+/')));
        
        return $payload;
    }
}

/**
 * Key class untuk Firebase JWT
 */
class Key
{
    public string $key;
    public string $algorithm;

    public function __construct(string $key, string $algorithm = 'HS256')
    {
        $this->key = $key;
        $this->algorithm = $algorithm;
    }
}
