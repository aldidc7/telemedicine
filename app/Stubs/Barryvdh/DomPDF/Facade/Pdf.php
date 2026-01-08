<?php

namespace Barryvdh\DomPDF\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Stub class untuk DomPDF Facade
 * Fallback ketika package tidak terinstall
 */
class Pdf extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dompdf';
    }

    /**
     * Load view dan convert ke PDF
     */
    public static function loadView(string $view, array $data = [])
    {
        return new static();
    }

    /**
     * Set paper size
     */
    public function setPaper(string $paper)
    {
        return $this;
    }

    /**
     * Set option
     */
    public function setOption(string $option, $value)
    {
        return $this;
    }

    /**
     * Output PDF
     */
    public function output()
    {
        return '';
    }

    /**
     * Download PDF
     */
    public function download(string $filename = 'document.pdf')
    {
        $contents = $this->output();
        return response()->streamDownload(
            function() use ($contents) { return $contents; },
            $filename
        );
    }

    /**
     * Stream to browser
     */
    public function stream(string $filename = 'document.pdf')
    {
        $contents = $this->output();
        return response()->stream(
            function() use ($contents) { return $contents; },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"'
            ]
        );
    }
}
