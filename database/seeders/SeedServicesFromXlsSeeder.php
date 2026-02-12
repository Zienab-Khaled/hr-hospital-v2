<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * سيدر مستقل: يقرأ ملف .xls (دليل الأسعار المعتمد بالأكواد) ويملأ جدول services.
 * توزيع الأعمدة حسب الملف: A=Cost Center, B=IOS BS (كود), E=BS Description E (اسم إنجليزي), F=BS Description A (اسم عربي), I=Price.
 */
class SeedServicesFromXlsSeeder extends Seeder
{
    private const BATCH = 500;
    /** حد الطول ليتوافق مع varchar(255) في الجدول. */
    private const MAX_NAME_LENGTH = 255;

    private int $created = 0;
    private int $updated = 0;
    /** @var array<int, true> */
    private ?array $departmentIds = null;

    private function findXls(): ?string
    {
        foreach ([base_path('official-codes.xls'), storage_path('app/official-codes.xls')] as $p) {
            if (is_file($p)) {
                return $p;
            }
        }
        $root = base_path();
        foreach (new \DirectoryIterator($root) as $f) {
            if ($f->isFile() && strtolower($f->getExtension()) === 'xls') {
                return $f->getPathname();
            }
        }
        return null;
    }

    private function departmentId(int $fromFile): ?int
    {
        if ($fromFile <= 0) {
            return null;
        }
        if ($this->departmentIds === null) {
            $ids = Department::pluck('id')->all();
            $this->departmentIds = array_flip($ids);
        }
        $firstId = $this->departmentIds !== [] ? (int) array_key_first($this->departmentIds) : null;
        return isset($this->departmentIds[$fromFile]) ? $fromFile : $firstId;
    }

    /** صف الرأس يحتوي "Cost Center" أو "IOS BS" أو "Price" */
    private function isHeaderRow(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $row): bool
    {
        $a = strtolower(trim((string) $sheet->getCell('A' . $row)->getValue()));
        $b = strtolower(trim((string) $sheet->getCell('B' . $row)->getValue()));
        return $a === 'cost center' || $b === 'ios bs' || str_contains($a, 'cost') || str_contains($b, 'ios');
    }

    public function run(): void
    {
        $path = $this->findXls();
        if (!$path) {
            $this->command?->warn('No .xls file in project root or official-codes.xls.');
            return;
        }

        $this->command?->info('Reading: ' . basename($path) . ' (A=Cost Center, B=Code, E=Name EN, F=Name AR, I=Price)');

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($path);

        $this->created = 0;
        $this->updated = 0;
        $batch = [];

        $sheetCount = $spreadsheet->getSheetCount();
        for ($s = 0; $s < $sheetCount; $s++) {
            $sheet = $spreadsheet->getSheet($s);
            $highest = $sheet->getHighestRow();
            $isFirstSheet = ($s === 0);

            for ($row = 1; $row <= $highest; $row++) {
                if ($isFirstSheet && $row === 1 && $this->isHeaderRow($sheet, $row)) {
                    continue;
                }

                $costCenter = (int) $sheet->getCell('A' . $row)->getValue();
                $code = trim((string) $sheet->getCell('B' . $row)->getValue());
                $nameEn = trim((string) $sheet->getCell('E' . $row)->getValue());
                $nameAr = trim((string) $sheet->getCell('F' . $row)->getValue());
                $price = (float) $sheet->getCell('I' . $row)->getValue();

                if ($code === '') {
                    continue;
                }

                $name = $nameEn !== '' ? $nameEn : $nameAr;
                if ($name === '') {
                    $name = $code;
                }
                $nameArVal = $nameAr !== '' ? $nameAr : $name;
                $batch[] = [
                    'code' => $code,
                    'name' => mb_substr($name, 0, self::MAX_NAME_LENGTH),
                    'name_ar' => mb_substr($nameArVal, 0, self::MAX_NAME_LENGTH),
                    'price' => $price,
                    'department_id' => $costCenter > 0 ? $this->departmentId($costCenter) : null,
                ];

                if (count($batch) >= self::BATCH) {
                    $this->flushBatch($batch);
                    $batch = [];
                }
            }
        }

        if (count($batch) > 0) {
            $this->flushBatch($batch);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $this->command?->info("Done. Created: {$this->created}, Updated: {$this->updated}");
    }

    /**
     * @param array<int, array{code: string, name: string, name_ar: string, price: float, department_id: int|null}> $batch
     */
    private function flushBatch(array $batch): void
    {
        DB::transaction(function () use ($batch) {
            foreach ($batch as $r) {
                $svc = Service::updateOrCreate(
                    ['code' => $r['code']],
                    [
                        'name' => $r['name'],
                        'name_ar' => $r['name_ar'],
                        'default_price' => $r['price'],
                        'department_id' => $r['department_id'],
                        'is_active' => true,
                    ]
                );
                $svc->wasRecentlyCreated ? $this->created++ : $this->updated++;
            }
        });
    }
}
