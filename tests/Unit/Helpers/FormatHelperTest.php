<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Helpers\FormatHelper;

/**
 * Unit Tests untuk FormatHelper
 * 
 * Test semua formatting functions untuk consistency
 * Coverage: 100%
 */
class FormatHelperTest extends TestCase
{
    /**
     * Test get string preview within limit
     */
    public function test_get_string_preview_within_limit(): void
    {
        $text = "Hello World";
        $preview = FormatHelper::getStringPreview($text);
        
        $this->assertEquals("Hello World", $preview);
    }
    
    /**
     * Test get string preview truncated
     */
    public function test_get_string_preview_truncated(): void
    {
        $text = "This is a very long text that needs to be truncated";
        $preview = FormatHelper::getStringPreview($text);
        
        $this->assertStringEndsWith('...', $preview);
        $this->assertLessThanOrEqual(53, strlen($preview)); // 50 + 3 for ...
    }
    
    /**
     * Test get string preview with custom length
     */
    public function test_get_string_preview_custom_length(): void
    {
        $text = "This is a very long text";
        $preview = FormatHelper::getStringPreview($text, 10);
        
        $this->assertEquals("This is a ...", $preview);
    }
    
    /**
     * Test get message preview
     */
    public function test_get_message_preview(): void
    {
        $content = "This is a very long message content that should be previewed";
        $preview = FormatHelper::getMessagePreview($content);
        
        $this->assertLessThanOrEqual(53, strlen($preview));
        $this->assertStringEndsWith('...', $preview);
    }
    
    /**
     * Test format currency
     */
    public function test_format_currency(): void
    {
        $formatted = FormatHelper::formatCurrency(1000000);
        
        $this->assertStringContainsString('Rp', $formatted);
        $this->assertStringContainsString('1.000.000', $formatted);
    }
    
    /**
     * Test format currency without symbol
     */
    public function test_format_currency_without_symbol(): void
    {
        $formatted = FormatHelper::formatCurrency(1000000, false);
        
        $this->assertStringNotContainsString('Rp', $formatted);
        $this->assertStringContainsString('1.000.000', $formatted);
    }
    
    /**
     * Test format percentage
     */
    public function test_format_percentage(): void
    {
        $formatted = FormatHelper::formatPercentage(85.5);
        
        $this->assertEquals("85.50%", $formatted);
    }
    
    /**
     * Test format percentage custom decimals
     */
    public function test_format_percentage_custom_decimals(): void
    {
        $formatted = FormatHelper::formatPercentage(85.555, 1);
        
        $this->assertEquals("85.6%", $formatted);
    }
    
    /**
     * Test format rating
     */
    public function test_format_rating(): void
    {
        $formatted = FormatHelper::formatRating(4.5);
        
        $this->assertStringContainsString('★', $formatted);
        $this->assertStringContainsString('4.50', $formatted);
    }
    
    /**
     * Test format rating perfect
     */
    public function test_format_rating_perfect(): void
    {
        $formatted = FormatHelper::formatRating(5.0);
        
        $this->assertStringContainsString('★', $formatted);
        $this->assertStringContainsString('5.00', $formatted);
    }
    
    /**
     * Test format phone number
     */
    public function test_format_phone_number(): void
    {
        $formatted = FormatHelper::formatPhoneNumber('08123456789');
        
        $this->assertStringContainsString('+62', $formatted);
        $this->assertStringContainsString('-', $formatted);
    }
    
    /**
     * Test format phone number with +62 prefix
     */
    public function test_format_phone_number_with_prefix(): void
    {
        $formatted = FormatHelper::formatPhoneNumber('+628123456789');
        
        $this->assertStringContainsString('+62', $formatted);
    }
    
    /**
     * Test format duration
     */
    public function test_format_duration(): void
    {
        // 1 hour 1 minute 1 second = 3661 seconds
        $formatted = FormatHelper::formatDuration(3661);
        
        $this->assertStringContainsString('1h', $formatted);
        $this->assertStringContainsString('1m', $formatted);
        $this->assertStringContainsString('1s', $formatted);
    }
    
    /**
     * Test format duration minutes only
     */
    public function test_format_duration_minutes_only(): void
    {
        $formatted = FormatHelper::formatDuration(300); // 5 minutes
        
        $this->assertStringContainsString('5m', $formatted);
    }
    
    /**
     * Test format file size KB
     */
    public function test_format_file_size_kb(): void
    {
        $formatted = FormatHelper::formatFileSize(1024);
        
        $this->assertStringContainsString('1', $formatted);
        $this->assertStringContainsString('KB', $formatted);
    }
    
    /**
     * Test format file size MB
     */
    public function test_format_file_size_mb(): void
    {
        $formatted = FormatHelper::formatFileSize(1048576); // 1 MB
        
        $this->assertStringContainsString('1', $formatted);
        $this->assertStringContainsString('MB', $formatted);
    }
    
    /**
     * Test format measurement
     */
    public function test_format_measurement(): void
    {
        $formatted = FormatHelper::formatMeasurement(180, 'cm');
        
        $this->assertStringContainsString('180', $formatted);
        $this->assertStringContainsString('cm', $formatted);
    }
    
    /**
     * Test format boolean true
     */
    public function test_format_boolean_true(): void
    {
        $formatted = FormatHelper::formatBoolean(true);
        
        $this->assertEquals("Yes", $formatted);
    }
    
    /**
     * Test format boolean false
     */
    public function test_format_boolean_false(): void
    {
        $formatted = FormatHelper::formatBoolean(false);
        
        $this->assertEquals("No", $formatted);
    }
    
    /**
     * Test format boolean custom text
     */
    public function test_format_boolean_custom_text(): void
    {
        $formatted = FormatHelper::formatBoolean(true, 'Aktif', 'Tidak Aktif');
        
        $this->assertEquals("Aktif", $formatted);
    }
    
    /**
     * Test format status uppercase
     */
    public function test_format_status_uppercase(): void
    {
        $formatted = FormatHelper::formatStatus('aktif', 'uppercase');
        
        $this->assertEquals("AKTIF", $formatted);
    }
    
    /**
     * Test format status ucfirst
     */
    public function test_format_status_ucfirst(): void
    {
        $formatted = FormatHelper::formatStatus('aktif', 'ucfirst');
        
        $this->assertEquals("Aktif", $formatted);
    }
    
    /**
     * Test generate slug
     */
    public function test_generate_slug(): void
    {
        $slug = FormatHelper::generateSlug('Konsultasi Dokter Gigi');
        
        $this->assertEquals('konsultasi-dokter-gigi', $slug);
    }
    
    /**
     * Test highlight search term
     */
    public function test_highlight_search_term(): void
    {
        $text = "Konsultasi Dokter Gigi";
        $highlighted = FormatHelper::highlightSearchTerm($text, 'Dokter');
        
        $this->assertStringContainsString('<mark>Dokter</mark>', $highlighted);
    }
    
    /**
     * Test get initials
     */
    public function test_get_initials(): void
    {
        $initials = FormatHelper::getInitials('Bambang Wijaya');
        
        $this->assertEquals('BW', $initials);
    }
    
    /**
     * Test get initials single name
     */
    public function test_get_initials_single_name(): void
    {
        $initials = FormatHelper::getInitials('Bambang');
        
        $this->assertEquals('B', $initials);
    }
    
    /**
     * Test get role label
     */
    public function test_get_role_label(): void
    {
        $this->assertEquals('Admin', FormatHelper::getRoleLabel('admin'));
        $this->assertEquals('Dokter', FormatHelper::getRoleLabel('dokter'));
        $this->assertEquals('Pasien', FormatHelper::getRoleLabel('pasien'));
    }
    
    /**
     * Test get status label appointment
     */
    public function test_get_status_label_appointment(): void
    {
        $this->assertEquals('Menunggu', FormatHelper::getStatusLabel('pending'));
        $this->assertEquals('Dikonfirmasi', FormatHelper::getStatusLabel('confirmed'));
        $this->assertEquals('Selesai', FormatHelper::getStatusLabel('completed'));
    }
    
    /**
     * Test get status label consultation
     */
    public function test_get_status_label_consultation(): void
    {
        $this->assertEquals('Aktif', FormatHelper::getStatusLabel('aktif'));
        $this->assertEquals('Selesai', FormatHelper::getStatusLabel('selesai'));
        $this->assertEquals('Dibatalkan', FormatHelper::getStatusLabel('dibatalkan'));
    }
}
