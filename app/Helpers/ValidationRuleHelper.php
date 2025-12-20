<?php

namespace App\Helpers;

use Illuminate\Validation\Rule;

/**
 * Advanced Input Validation Helper
 * 
 * Menyediakan reusable validation rules yang lebih ketat
 * untuk mencegah injection attacks dan data corruption
 */
class ValidationRuleHelper
{
    /**
     * Sanitize string input (remove potentially dangerous characters)
     */
    public static function sanitizedString(int $minLength = 1, int $maxLength = 255): array
    {
        return [
            'string',
            "min:$minLength",
            "max:$maxLength",
            'regex:/^[a-zA-Z0-9\s\-\.\,\(\)\'\"&@\/]*$/', // Allow common characters
        ];
    }

    /**
     * Phone number validation (Indonesia format)
     */
    public static function phoneNumber(): array
    {
        return [
            'regex:/^(\+62|62|0)[0-9]{7,11}$/',
            'string',
        ];
    }

    /**
     * Safe email validation
     */
    public static function email(): array
    {
        return [
            'email:rfc,dns',
            'max:255',
        ];
    }

    /**
     * Strong password validation
     * - Min 8 characters
     * - At least 1 uppercase
     * - At least 1 number
     * - At least 1 special character
     */
    public static function strongPassword(): array
    {
        return [
            'min:8',
            'max:255',
            'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[\W_])/',
        ];
    }

    /**
     * Medical/Health text validation
     */
    public static function medicalText(int $minLength = 10, int $maxLength = 5000): array
    {
        return [
            'string',
            "min:$minLength",
            "max:$maxLength",
            'regex:/^[a-zA-Z0-9\s\-\.\,\(\)\:\/\'\"&\+\=\%]*$/',
        ];
    }

    /**
     * Doctor specialization validation
     */
    public static function specialization(): array
    {
        return [
            'string',
            'max:100',
            Rule::in([
                'Umum',
                'Gigi',
                'Mata',
                'THT',
                'Jantung',
                'Paru-paru',
                'Saraf',
                'Kulit',
                'Anak',
                'Kandungan',
                'Orthopedi',
                'Bedah',
                'Psikiatri',
                'Rehabilitasi Medis',
                'Urologi',
                'Gastroenterologi',
                'Radiologi',
                'Patologi Klinik',
                'Anestesiologi',
                'Lainnya',
            ]),
        ];
    }

    /**
     * Consultation type validation
     */
    public static function consultationType(): array
    {
        return [
            'string',
            Rule::in(['text', 'audio', 'video']),
        ];
    }

    /**
     * File upload validation
     */
    public static function documentFile(int $maxSize = 10240): array // 10 MB
    {
        return [
            'file',
            "max:$maxSize",
            'mimes:pdf,doc,docx,jpg,jpeg,png',
        ];
    }

    /**
     * Rating validation (1-5 stars)
     */
    public static function rating(): array
    {
        return [
            'integer',
            'min:1',
            'max:5',
        ];
    }

    /**
     * URL validation
     */
    public static function url(): array
    {
        return [
            'url',
            'max:2048',
            'regex:/^https?:\/\/.+/',
        ];
    }

    /**
     * Indonesian ID number (KTP) validation
     */
    public static function ktpNumber(): array
    {
        return [
            'string',
            'regex:/^[0-9]{16}$/',
        ];
    }

    /**
     * Medical license (SKP) validation
     */
    public static function skpNumber(): array
    {
        return [
            'string',
            'regex:/^[0-9]{10,20}$/',
        ];
    }
}
