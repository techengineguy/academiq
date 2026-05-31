<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfService
{
    /**
     * Generate a PDF from an HTML string and return the file path.
     */
    public function generateFromHtml(string $html, string $filename): string
    {
        $path = 'documents/' . $filename;
        $fullPath = storage_path('app/public/' . $path);

        // Ensure directory exists
        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        Browsershot::html($html)
            ->format('A4')
            ->margins(15, 15, 15, 15)
            ->showBackground()
            ->savePdf($fullPath);

        return $path;
    }

    /**
     * Generate a PDF from a Blade view and return the file path.
     */
    public function generateFromView(string $view, array $data, string $filename): string
    {
        $html = view($view, $data)->render();

        return $this->generateFromHtml($html, $filename);
    }

    /**
     * Generate a PDF and return the raw content for download.
     */
    public function downloadFromHtml(string $html): string
    {
        return Browsershot::html($html)
            ->format('A4')
            ->margins(15, 15, 15, 15)
            ->showBackground()
            ->pdf();
    }
}
