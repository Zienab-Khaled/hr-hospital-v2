<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\UnsuccessfulCommandException;

class IdentityDocumentExtractor
{
    /** Allowed image MIME types for OCR */
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Extract name and identity number from an identity document image using OCR.
     * Returns structured data for form auto-fill; works best with clear photos of Saudi ID/Iqama/Passport.
     * Requires Tesseract OCR installed (e.g. Windows: https://github.com/UB-Mannheim/tesseract/wiki).
     */
    public function extract(UploadedFile $file): array
    {
        if (!in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            return [
                'success' => false,
                'message' => app()->getLocale() === 'ar'
                    ? 'نوع الملف غير مدعوم. استخدم صورة (JPG, PNG, WebP).'
                    : 'File type not supported. Use an image (JPG, PNG, WebP).',
                'data' => [],
            ];
        }

        $path = $file->getRealPath();
        if (!$path || !is_readable($path)) {
            return [
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'تعذر قراءة الملف.' : 'Could not read file.',
                'data' => [],
            ];
        }

        $executable = config('services.tesseract.executable', 'tesseract');

        // If custom path is set, verify the file exists (helps with wrong path / drive)
        if ($executable !== 'tesseract' && $executable !== '') {
            $normalized = str_replace('\\', '/', $executable);
            if (!is_file($normalized)) {
                $msg = app()->getLocale() === 'ar'
                    ? 'Tesseract غير موجود في المسار: '
                    : 'Tesseract not found at path: ';
                return [
                    'success' => false,
                    'message' => $msg . $normalized . (app()->getLocale() === 'ar' ? ' تحقق من TESSERACT_EXECUTABLE في .env' : ' (check TESSERACT_EXECUTABLE in .env)'),
                    'data' => [],
                ];
            }
            $executable = $normalized;
        }

        try {
            $rawText = $this->runTesseract($path, $executable);
        } catch (\Throwable $e) {
            report($e);
            $detail = config('app.debug') ? ' ' . trim(preg_replace('/\s+/', ' ', $e->getMessage())) : '';
            $hint = $executable === 'tesseract' || $executable === ''
                ? (app()->getLocale() === 'ar'
                    ? ' عيّن TESSERACT_EXECUTABLE في .env إلى المسار الكامل (مثال: C:/Program Files/Tesseract-OCR/tesseract.exe).'
                    : ' Set TESSERACT_EXECUTABLE in .env to the full path (e.g. C:/Program Files/Tesseract-OCR/tesseract.exe).')
                : '';
            return [
                'success' => false,
                'message' => (app()->getLocale() === 'ar'
                    ? 'تعذر تشغيل الاستخراج.'
                    : 'Extraction failed.') . $hint . $detail,
                'data' => ['raw_text' => ''],
            ];
        }

        $parsed = $this->parseExtractedText($rawText);

        return [
            'success' => true,
            'message' => app()->getLocale() === 'ar'
                ? 'تم استخراج البيانات. راجع الحقول وأكمل إن لزم.'
                : 'Data extracted. Review and complete fields if needed.',
            'data' => $parsed,
        ];
    }

    /**
     * Parse OCR text for Saudi identity document patterns: 10-digit ID and names.
     */
    private function parseExtractedText(string $text): array
    {
        $data = [
            'name' => '',
            'name_ar' => '',
            'identity_value' => '',
            'identity_type' => null,
            'raw_text' => $text,
        ];

        $lines = preg_split('/\r\n|\n|\r/', $text);
        $lines = array_map('trim', array_filter($lines));

        // Saudi ID / Iqama: 10 digits, first digit 1 (citizen) or 2 (resident)
        if (preg_match('/\b([12]\d{9})\b/', $text, $m)) {
            $data['identity_value'] = $m[1];
            $data['identity_type'] = $m[1][0] === '1' ? 'national_id' : 'iqama';
        }

        // Other 10-digit numbers (passport/visa might be different format; take first 10-digit if no 1/2 match)
        if ($data['identity_value'] === '' && preg_match('/\b(\d{10})\b/', $text, $m)) {
            $data['identity_value'] = $m[1];
        }

        // Name candidates: lines with Arabic or Latin letters, reasonable length, not only digits
        $nameEn = '';
        $nameAr = '';
        foreach ($lines as $line) {
            if (strlen($line) < 2 || strlen($line) > 80) {
                continue;
            }
            $digitsOnly = preg_replace('/\D/', '', $line);
            if (strlen($digitsOnly) === strlen($line)) {
                continue;
            }
            if (preg_match('/[\x{0600}-\x{06FF}]/u', $line)) {
                if ($nameAr === '' && mb_strlen($line) >= 2) {
                    $nameAr = $line;
                }
            } else {
                if ($nameEn === '' && preg_match('/[A-Za-z]/', $line)) {
                    $nameEn = $line;
                }
            }
        }

        if ($nameEn !== '') {
            $data['name'] = $nameEn;
        }
        if ($nameAr !== '') {
            $data['name_ar'] = $nameAr;
        }
        if ($data['name'] === '' && $data['name_ar'] !== '') {
            $data['name'] = $data['name_ar'];
        }
        if ($data['name_ar'] === '' && $data['name'] !== '') {
            $data['name_ar'] = $data['name'];
        }

        return $data;
    }

    /**
     * Run Tesseract: try Arabic + English first; if that fails (e.g. Arabic not installed), try English only.
     */
    private function runTesseract(string $imagePath, string $executable): string
    {
        $tesseract = new TesseractOCR($imagePath);
        if ($executable !== 'tesseract' && $executable !== '') {
            $tesseract->executable($executable);
        }
        $tesseract->lang('ara', 'eng');

        try {
            return $tesseract->run();
        } catch (UnsuccessfulCommandException $e) {
            $msg = $e->getMessage();
            // Missing language data (e.g. ara.traineddata) — retry with English only
            if (stripos($msg, 'ara') !== false || stripos($msg, 'tessdata') !== false || stripos($msg, 'language') !== false || stripos($msg, 'Unable to load') !== false) {
                $tesseract2 = new TesseractOCR($imagePath);
                if ($executable !== 'tesseract' && $executable !== '') {
                    $tesseract2->executable($executable);
                }
                $tesseract2->lang('eng');
                return $tesseract2->run();
            }
            throw $e;
        }
    }
}
