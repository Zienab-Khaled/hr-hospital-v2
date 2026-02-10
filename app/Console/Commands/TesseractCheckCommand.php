<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TesseractCheckCommand extends Command
{
    protected $signature = 'tesseract:check';
    protected $description = 'Verify Tesseract OCR is installed and reachable (for identity document extraction).';

    public function handle(): int
    {
        $executable = config('services.tesseract.executable', 'tesseract');

        $this->info('Tesseract executable config: ' . ($executable ?: '(default: tesseract in PATH)'));

        if ($executable !== 'tesseract' && $executable !== '') {
            $normalized = str_replace('\\', '/', $executable);
            if (!is_file($normalized)) {
                $this->error('File not found: ' . $normalized);
                $this->line('Fix: set TESSERACT_EXECUTABLE in .env to the correct path (use forward slashes).');
                return self::FAILURE;
            }
            $this->info('File exists: ' . $normalized);
        }

        try {
            $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR;
            if ($executable !== 'tesseract' && $executable !== '') {
                $ocr->executable(str_replace('\\', '/', $executable));
            }
            $version = $ocr->version();
            $this->info('Tesseract version: ' . $version);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Tesseract check failed: ' . $e->getMessage());
            $this->line('Ensure Tesseract is installed. Windows: https://github.com/UB-Mannheim/tesseract/wiki');
            return self::FAILURE;
        }
    }
}
