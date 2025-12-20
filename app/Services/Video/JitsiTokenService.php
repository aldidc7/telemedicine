<?php

/** 
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUnused
 */

namespace App\Services\Video;

use Firebase\JWT\JWT;
use Firebase\JWT\Key as JWTKey;
use Exception;

/**
 * Jitsi JWT Token Service
 * 
 * Generate JWT tokens untuk authentikasi ke Jitsi Meet
 */
class JitsiTokenService
{
    private string $secret;
    private string $appId;
    private int $ttl;

    public function __construct()
    {
        $this->secret = config('jitsi.secret_key');
        $this->appId = config('jitsi.app_id');
        $this->ttl = config('jitsi.token_ttl', 86400);

        if (!$this->secret || $this->secret === 'your-secret-key-change-in-production') {
            throw new Exception('Jitsi secret key belum dikonfigurasi. Silakan set JITSI_SECRET_KEY di .env');
        }
    }

    /**
     * Generate JWT token untuk video session
     * 
     * @param int $userId User ID (doctor atau patient)
     * @param string $userName User name untuk display
     * @param string $userEmail User email
     * @param string $roomName Nama ruangan (consultation room)
     * @param string|null $userRole Role: 'doctor' atau 'patient'
     * 
     * @return string JWT token
     */
    public function generateToken(
        int $userId,
        string $userName,
        string $userEmail,
        string $roomName,
        ?string $userRole = null
    ): string {
        $now = time();
        $exp = $now + $this->ttl;

        $payload = [
            'iss' => config('jitsi.app_id'),
            'sub' => config('jitsi.server_url'),
            'aud' => 'jitsi',
            'room' => $roomName,
            'iat' => $now,
            'exp' => $exp,
            'name' => $userName,
            'email' => $userEmail,
            'user_id' => (string)$userId,
        ];

        // Add user properties untuk Jitsi
        $payload['context'] = [
            'user' => [
                'id' => (string)$userId,
                'name' => $userName,
                'email' => $userEmail,
                'avatar' => $this->getUserAvatar($userId),
            ],
        ];

        if ($userRole) {
            $payload['context']['user']['role'] = $userRole;
        }

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    /**
     * Generate token untuk moderator (doctor)
     * 
     * @param int $doctorId
     * @param string $doctorName
     * @param string $doctorEmail
     * @param string $roomName
     * 
     * @return string JWT token
     */
    public function generateModeratorToken(
        int $doctorId,
        string $doctorName,
        string $doctorEmail,
        string $roomName
    ): string {
        return $this->generateToken(
            $doctorId,
            $doctorName,
            $doctorEmail,
            $roomName,
            'moderator'
        );
    }

    /**
     * Generate token untuk peserta biasa (patient)
     * 
     * @param int $patientId
     * @param string $patientName
     * @param string $patientEmail
     * @param string $roomName
     * 
     * @return string JWT token
     */
    public function generateParticipantToken(
        int $patientId,
        string $patientName,
        string $patientEmail,
        string $roomName
    ): string {
        return $this->generateToken(
            $patientId,
            $patientName,
            $patientEmail,
            $roomName,
            'participant'
        );
    }

    /**
     * Verifikasi JWT token
     * 
     * @param string $token
     * 
     * @return object Token payload
     */
    public function verifyToken(string $token): object
    {
        try {
            return JWT::decode($token, new JWTKey($this->secret, 'HS256'));
        } catch (Exception $e) {
            throw new Exception('Invalid JWT token: ' . $e->getMessage());
        }
    }

    /**
     * Dekode token tanpa verifikasi (untuk debugging)
     * 
     * @param string $token
     * 
     * @return object Token payload
     */
    public function decodeToken(string $token): object
    {
        try {
            // Decode tanpa verifikasi
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                throw new Exception('Invalid token format');
            }

            $payload = json_decode(
                base64_decode(strtr($parts[1], '-_', '+/'))
            );

            return $payload;
        } catch (Exception $e) {
            throw new Exception('Failed to decode token: ' . $e->getMessage());
        }
    }

    /**
     * Get user avatar URL
     * 
     * @param int $userId
     * 
     * @return string|null Avatar URL
     */
    private function getUserAvatar(?int $userId): ?string
    {
        if (!$userId) {
            return null;
        }

        try {
            $user = \App\Models\User::find($userId);
            if ($user && method_exists($user, 'getAvatarUrl')) {
                return $user->getAvatarUrl();
            }
        } catch (Exception $e) {
            // Return null jika ada error
        }

        return null;
    }

    /**
     * Format room name dari consultation
     * 
     * @param int $consultationId
     * 
     * @return string Room name (safe for Jitsi)
     */
    public static function formatRoomName(int $consultationId): string
    {
        // Format: consultation-{id} (alphanumeric, hyphens only)
        return 'consultation-' . $consultationId;
    }

    /**
     * Generate room name dari session ID
     * 
     * @param int $sessionId
     * 
     * @return string Room name
     */
    public static function generateRoomNameFromSessionId(int $sessionId): string
    {
        return 'session-' . $sessionId;
    }

    /**
     * Check apakah token sudah expire
     * 
     * @param object $payload Token payload
     * 
     * @return bool
     */
    public static function isTokenExpired(object $payload): bool
    {
        return isset($payload->exp) && $payload->exp < time();
    }

    /**
     * Get token expiration time
     * 
     * @param object $payload
     * 
     * @return int|null Waktu expire dalam detik (unix timestamp)
     */
    public static function getTokenExpiration(object $payload): ?int
    {
        return $payload->exp ?? null;
    }
}
