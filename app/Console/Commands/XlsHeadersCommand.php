<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class XlsHeadersCommand extends Command
{
    protected $signature = 'xls:headers {--path= : Path to .xls file }';
    protected $description = 'Print first 2 rows of project .xls file to see column names (A, B, C...).';

    public function handle(): int
    {
        $path = $this->option('path');
        if (!$path) {
            foreach ([base_path('official-codes.xls'), storage_path('app/official-codes.xls')] as $p) {
                if (is_file($p)) {
                    $path = $p;
                    break;
                }
            }
            if (!$path) {
                $root = base_path();
                foreach (new \DirectoryIterator($root) as $f) {
                    if ($f->isFile() && strtolower($f->getExtension()) === 'xls') {
                        $path = $f->getPathname();
                        break;
                    }
                }
            }
        }
        if (!$path || !is_file($path)) {
            $this->error('No .xls file found. Put file in project root or use --path=...');
            return self::FAILURE;
        }

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $maxCol = $sheet->getHighestColumn();
        $maxColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($maxCol);

        $this->info('File: ' . basename($path));
        $this->info('Columns: A to ' . $maxCol . ' (' . $maxColIndex . ' columns)');
        $this->line('');

        for ($row = 1; $row <= 2; $row++) {
            $this->line('--- Row ' . $row . ' ---');
            for ($c = 1; $c <= min($maxColIndex, 15); $c++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $val = $sheet->getCell($colLetter . $row)->getValue();
                $this->line('  ' . $colLetter . ' = ' . (is_scalar($val) ? (string) $val : json_encode($val)));
            }
            if ($maxColIndex > 15) {
                $this->line('  ... (' . ($maxColIndex - 15) . ' more columns)');
            }
            $this->line('');
        }

        $spreadsheet->disconnectWorksheets();
        return self::SUCCESS;
    }
}
