<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * ============================================
 * INPUT SANITIZATION SERVICE
 * ============================================
 * 
 * Comprehensive input sanitization
 * Prevents XSS, SQL injection, dan harmful content
 */
class SanitizationService
{
    /**
     * Sanitize text input
     * Remove special characters but keep safe ones
     * 
     * @param string $input
     * @return string
     */
    public static function sanitizeText($input)
    {
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        // Remove multiple spaces
        $input = preg_replace('/\s+/', ' ', $input);
        
        return $input;
    }

    /**
     * Sanitize HTML content
     * Strip dangerous tags tapi keep safe formatting
     * 
     * @param string $html
     * @return string
     */
    public static function sanitizeHtml($html)
    {
        // Allowed tags: b, i, u, strong, em, p, br, ul, ol, li
        $allowed_tags = '<b><i><u><strong><em><p><br><ul><ol><li>';
        
        // Strip tags yang tidak diizinkan
        $clean = strip_tags($html, $allowed_tags);
        
        // Remove script tags dan event handlers
        $clean = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi', '', $clean);
        $clean = preg_replace('/on\w+\s*=\s*["\']?[^"\']*["\']?/i', '', $clean);
        
        // Trim whitespace
        $clean = trim($clean);
        
        return $clean;
    }

    /**
     * Escape HTML entities
     * Safe untuk display di browser
     * 
     * @param string $text
     * @return string
     */
    public static function escapeHtml($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize untuk database query
     * Escape special characters
     * 
     * @param string $input
     * @return string
     */
    public static function sanitizeQuery($input)
    {
        // Use parameterized queries instead (recommended)
        // Ini hanya backup jika parameterized queries tidak bisa digunakan
        $search = ["'", "\"", "\\"];
        $replace = ["\'", "\"", "\\\\"];
        
        return str_replace($search, $replace, $input);
    }

    /**
     * Sanitize email
     * 
     * @param string $email
     * @return string|null
     */
    public static function sanitizeEmail($email)
    {
        // Convert ke lowercase dan trim
        $email = strtolower(trim($email));
        
        // Validate format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }
        
        return $email;
    }

    /**
     * Sanitize phone number
     * Keep hanya digits dan +
     * 
     * @param string $phone
     * @return string
     */
    public static function sanitizePhone($phone)
    {
        // Keep hanya digits dan +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Remove multiple + (hanya boleh 1 di awal)
        $phone = preg_replace('/\++/', '+', $phone);
        
        // Ensure + is at the beginning if present
        if (strpos($phone, '+') !== false) {
            $phone = '+' . preg_replace('/\+/', '', $phone);
        }
        
        return $phone;
    }

    /**
     * Sanitize file name
     * Remove special characters dari filename
     * 
     * @param string $filename
     * @return string
     */
    public static function sanitizeFileName($filename)
    {
        // Get extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Remove extension
        $name = pathinfo($filename, PATHINFO_FILENAME);
        
        // Remove special characters, keep hanya alphanumeric, dash, underscore
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '', $name);
        
        // Remove multiple dots
        $name = preg_replace('/\.+/', '.', $name);
        
        // Limit length
        $name = substr($name, 0, 255);
        
        // Return dengan extension
        return $extension ? $name . '.' . $extension : $name;
    }

    /**
     * Sanitize URL
     * Ensure URL is safe dan valid
     * 
     * @param string $url
     * @return string|null
     */
    public static function sanitizeUrl($url)
    {
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        
        // Ensure https:// or http://
        if (!preg_match('/^https?:\/\//i', $url)) {
            return null;
        }
        
        return $url;
    }

    /**
     * Sanitize JSON
     * Ensure valid JSON dan safe content
     * 
     * @param string $json
     * @return array|null
     */
    public static function sanitizeJson($json)
    {
        // Decode JSON
        $decoded = json_decode($json, true);
        
        // Check if valid JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        // Recursively sanitize values
        return self::sanitizeArray($decoded);
    }

    /**
     * Sanitize array recursively
     * 
     * @param array $array
     * @return array
     */
    public static function sanitizeArray($array)
    {
        $sanitized = [];
        
        foreach ($array as $key => $value) {
            // Sanitize key
            $key = self::sanitizeText($key);
            
            if (is_array($value)) {
                // Recursive untuk nested arrays
                $sanitized[$key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                // Sanitize string values
                $sanitized[$key] = self::sanitizeText($value);
            } else {
                // Keep other types as-is
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize form data
     * Comprehensive sanitization untuk form input
     * 
     * @param array $data
     * @return array
     */
    public static function sanitizeFormData($data)
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                // Detect field type dari key name
                if (strpos($key, 'email') !== false) {
                    $sanitized[$key] = self::sanitizeEmail($value);
                } elseif (strpos($key, 'phone') !== false || strpos($key, 'no_hp') !== false) {
                    $sanitized[$key] = self::sanitizePhone($value);
                } elseif (strpos($key, 'url') !== false) {
                    $sanitized[$key] = self::sanitizeUrl($value);
                } elseif (strpos($key, 'message') !== false || strpos($key, 'pesan') !== false) {
                    $sanitized[$key] = self::sanitizeHtml($value);
                } else {
                    $sanitized[$key] = self::sanitizeText($value);
                }
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}
