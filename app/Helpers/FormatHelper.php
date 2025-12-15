<?php

namespace App\Helpers;

use Illuminate\Support\Str;

/**
 * String & Format Helper Functions
 * 
 * Centralize string operations dan formatting untuk DRY principle
 * 
 * Updated: Session 5 - Code Refactoring Phase
 */
class FormatHelper
{
    /**
     * Get preview dari string (truncate dengan ellipsis)
     * 
     * Duplikasi pattern di MessageService.php line 56 & 80
     * Sekarang gunakan centralized function
     * 
     * @param string $text Text yang ingin di-preview
     * @param int|null $length Max length (default dari config)
     * @return string Truncated text
     */
    public static function getStringPreview(string $text, ?int $length = null): string
    {
        $length = $length ?? config('application.STRING_PREVIEW_LENGTH', 50);
        
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . '...';
    }
    
    /**
     * Get message preview
     * 
     * Khusus untuk message content preview
     * 
     * @param string $content Message content
     * @return string Preview text
     */
    public static function getMessagePreview(string $content): string
    {
        $length = config('application.MESSAGE_PREVIEW_LENGTH', 50);
        return self::getStringPreview($content, $length);
    }
    
    /**
     * Format currency (IDR)
     * 
     * Contoh: 1000000 → "Rp 1.000.000"
     * 
     * @param float $amount Amount dalam rupiah
     * @param bool $withSymbol Include symbol (default: true)
     * @return string Formatted currency string
     */
    public static function formatCurrency(float $amount, bool $withSymbol = true): string
    {
        $formatted = number_format($amount, 0, ',', '.');
        
        return $withSymbol ? "Rp {$formatted}" : $formatted;
    }
    
    /**
     * Format percentage
     * 
     * Contoh: 85.5 → "85.50%"
     * 
     * @param float $value Percentage value
     * @param int $decimals Desimal places (default: 2)
     * @return string Formatted percentage
     */
    public static function formatPercentage(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals) . '%';
    }
    
    /**
     * Format rating dengan stars
     * 
     * Contoh: 4.5 → "★★★★☆ 4.50"
     * 
     * @param float $rating Rating value (0-5)
     * @return string Rating dengan stars
     */
    public static function formatRating(float $rating): string
    {
        $fullStars = floor($rating);
        $hasHalfStar = ($rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
        
        $stars = str_repeat('★', $fullStars);
        if ($hasHalfStar) {
            $stars .= '★'; // Simplified - bisa juga pakai ☆/☆
        }
        $stars .= str_repeat('☆', $emptyStars);
        
        return "{$stars} " . number_format($rating, 2);
    }
    
    /**
     * Format phone number
     * 
     * Contoh: "08123456789" → "+62-812-3456-789"
     * 
     * @param string $phone Phone number (bisa dengan/tanpa +62)
     * @return string Formatted phone number
     */
    public static function formatPhoneNumber(string $phone): string
    {
        // Remove semua non-digit
        $phone = preg_replace('/\D/', '', $phone);
        
        // Jika start dengan 0, ganti dengan 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        // Format: +62-812-3456-789
        if (strlen($phone) === 12) {
            return '+' . substr($phone, 0, 2) . '-' . substr($phone, 2, 3) . '-' . substr($phone, 5, 4) . '-' . substr($phone, 9, 3);
        }
        
        return $phone;
    }
    
    /**
     * Format duration time
     * 
     * Contoh: 3661 → "1h 1m 1s"
     * 
     * @param int $seconds Duration dalam detik
     * @return string Formatted duration string
     */
    public static function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        $parts = [];
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($minutes > 0) $parts[] = "{$minutes}m";
        if ($secs > 0) $parts[] = "{$secs}s";
        
        return implode(' ', $parts) ?: '0s';
    }
    
    /**
     * Format file size
     * 
     * Contoh: 1048576 → "1 MB"
     * 
     * @param int $bytes File size dalam bytes
     * @param int $decimals Desimal places (default: 2)
     * @return string Formatted file size
     */
    public static function formatFileSize(int $bytes, int $decimals = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $bytes;
        
        foreach ($units as $unit) {
            if ($size < 1024) {
                return number_format($size, $decimals) . ' ' . $unit;
            }
            $size /= 1024;
        }
        
        return number_format($size, $decimals) . ' PB';
    }
    
    /**
     * Format medical measurement
     * 
     * Contoh: 180 → "180 cm", 70 → "70 kg"
     * 
     * @param float $value Value
     * @param string $unit Unit (cm, kg, mmHg, bpm, etc)
     * @return string Formatted measurement
     */
    public static function formatMeasurement(float $value, string $unit): string
    {
        return number_format($value, 2) . ' ' . $unit;
    }
    
    /**
     * Format boolean sebagai yes/no
     * 
     * @param bool $value Boolean value
     * @param string $yesText Text untuk true (default: "Yes")
     * @param string $noText Text untuk false (default: "No")
     * @return string Formatted boolean
     */
    public static function formatBoolean(bool $value, string $yesText = 'Yes', string $noText = 'No'): string
    {
        return $value ? $yesText : $noText;
    }
    
    /**
     * Format as indonesian word
     * 
     * Contoh: 'aktif' → 'Aktif'
     * 
     * @param string $text Text yang ingin diformat
     * @param string $case Case (ucfirst, uppercase, lowercase)
     * @return string Formatted text
     */
    public static function formatStatus(string $text, string $case = 'ucfirst'): string
    {
        return match ($case) {
            'uppercase' => strtoupper($text),
            'lowercase' => strtolower($text),
            'ucfirst' => ucfirst($text),
            default => $text,
        };
    }
    
    /**
     * Generate slug dari text
     * 
     * Contoh: "Konsultasi Dokter Gigi" → "konsultasi-dokter-gigi"
     * 
     * @param string $text Text
     * @return string Slug
     */
    public static function generateSlug(string $text): string
    {
        return Str::slug($text);
    }
    
    /**
     * Highlight search term dalam text
     * 
     * Contoh: ("Konsultasi Dokter", "Dokter") → "Konsultasi <mark>Dokter</mark>"
     * 
     * @param string $text Original text
     * @param string $searchTerm Search term untuk di-highlight
     * @param string $tag HTML tag untuk wrapping (default: mark)
     * @return string HTML dengan highlighting
     */
    public static function highlightSearchTerm(string $text, string $searchTerm, string $tag = 'mark'): string
    {
        if (empty($searchTerm)) {
            return $text;
        }
        
        $pattern = '/(' . preg_quote($searchTerm, '/') . ')/i';
        return preg_replace($pattern, "<{$tag}>$1</{$tag}>", $text);
    }
    
    /**
     * Get initials dari nama
     * 
     * Contoh: "Bambang Wijaya" → "BW"
     * 
     * @param string $name Full name
     * @return string Initials (uppercase)
     */
    public static function getInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        return substr($initials, 0, 2); // Max 2 initials
    }
    
    /**
     * Get role label dalam bahasa Indonesia
     * 
     * @param string $role Role key (admin, dokter, pasien)
     * @return string Role label
     */
    public static function getRoleLabel(string $role): string
    {
        return match (strtolower($role)) {
            'admin' => 'Admin',
            'dokter' => 'Dokter',
            'pasien' => 'Pasien',
            default => ucfirst($role),
        };
    }
    
    /**
     * Get status label dalam bahasa Indonesia
     * 
     * @param string $status Status value
     * @return string Status label
     */
    public static function getStatusLabel(string $status): string
    {
        return match (strtolower($status)) {
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'no-show' => 'Tidak Hadir',
            'aktif' => 'Aktif',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            'expired' => 'Expired',
            'archived' => 'Diarsipkan',
            'active' => 'Aktif',
            default => ucfirst($status),
        };
    }
}
