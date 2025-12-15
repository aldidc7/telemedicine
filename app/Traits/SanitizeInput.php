<?php

namespace App\Traits;

use Illuminate\Support\HtmlString;

trait SanitizeInput
{
    /**
     * Sanitize user input to prevent XSS attacks
     * 
     * Uses HTMLPurifier-like approach for rich text
     * Basic escaping for simple text fields
     */
    public function sanitizeInput(string $input, string $type = 'text'): string
    {
        // Remove null bytes
        $input = str_replace("\0", "", $input);

        switch ($type) {
            case 'html':
                // Allow only safe HTML tags
                return $this->sanitizeHtml($input);

            case 'text':
                // Escape all HTML entities
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

            case 'url':
                // Validate and escape URL
                return $this->sanitizeUrl($input);

            case 'email':
                // Validate email format
                return filter_var($input, FILTER_SANITIZE_EMAIL);

            case 'number':
                // Keep only numbers and decimal point
                return preg_replace('/[^0-9.]/', '', $input);

            default:
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Sanitize HTML content - allow only safe tags
     */
    private function sanitizeHtml(string $html): string
    {
        $allowedTags = [
            '<b>', '</b>',
            '<i>', '</i>',
            '<u>', '</u>',
            '<p>', '</p>',
            '<br>', '<br/>',
            '<strong>', '</strong>',
            '<em>', '</em>',
            '<ul>', '</ul>',
            '<ol>', '</ol>',
            '<li>', '</li>',
            '<a>', '</a>',
            '<h1>', '</h1>',
            '<h2>', '</h2>',
            '<h3>', '</h3>',
        ];

        // First, strip all tags
        $html = strip_tags($html, implode('', $allowedTags));

        // Remove dangerous attributes from allowed tags
        $html = preg_replace('/<(b|i|u|p|br|strong|em|ul|ol|li|a|h[1-3])[^>]*>/i', '<$1>', $html);

        // Specific handling for <a> tags - allow href but validate
        $html = preg_replace_callback(
            '/<a\s+href=["\']([^"\']*)["\'][^>]*>/i',
            function ($matches) {
                $url = $this->sanitizeUrl($matches[1]);
                return '<a href="' . $url . '">';
            },
            $html
        );

        return $html;
    }

    /**
     * Sanitize URL - prevent javascript: and data: schemes
     */
    private function sanitizeUrl(string $url): string
    {
        // Decode URL to check for encoded dangerous protocols
        $url = urldecode($url);

        // Block dangerous protocols
        $dangerousProtocols = [
            'javascript:',
            'data:',
            'vbscript:',
            'file:',
            'about:',
        ];

        foreach ($dangerousProtocols as $protocol) {
            if (stripos($url, $protocol) === 0) {
                return '';
            }
        }

        // Validate URL format
        if (filter_var($url, FILTER_VALIDATE_URL) || strpos($url, '/') === 0 || strpos($url, '#') === 0) {
            return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        }

        return '';
    }

    /**
     * Escape output for safe display in HTML
     */
    public function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escape output for safe display in JSON
     */
    public function escapeJson(string $text): string
    {
        return json_encode($text, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APO | JSON_HEX_QUOT);
    }
}
