<?php

namespace Database\Seeders;

use App\Services\OfficialCodesImporter;
use Illuminate\Database\Seeder;

class OfficialCodesSeeder extends Seeder
{
    /**
     * Find official codes .xls file: explicit paths first, then any .xls in project root.
     */
    private function findFile(): ?string
    {
        $try = [
            base_path('official-codes.xls'),
            storage_path('app/official-codes.xls'),
        ];
        foreach ($try as $p) {
            if (is_file($p)) {
                return $p;
            }
        }
        // أي ملف .xls في جذر المشروع (يتجنب مشكلة ترميز اسم الملف)
        $root = base_path();
        foreach (new \DirectoryIterator($root) as $f) {
            if ($f->isFile() && strtolower($f->getExtension()) === 'xls') {
                return $f->getPathname();
            }
        }
        return null;
    }

    public function run(): void
    {
        $path = $this->findFile();

        if (!$path) {
            $this->command?->warn('Official codes file not found. Put a .xls file in project root or as official-codes.xls / storage/app/official-codes.xls');
            return;
        }

        $this->command?->info('Importing official codes from: ' . basename($path));

        $importer = new OfficialCodesImporter();
        [$created, $updated] = $importer->import($path, 'xls');

        $this->command?->info("Done. Created: {$created}, Updated: {$updated}");
    }
}
