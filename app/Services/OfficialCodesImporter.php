<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class OfficialCodesImporter
{
    private const BATCH_SIZE = 500;

    private const HEADER_VALUES = ['code', 'كود', 'id', 'رقم'];

    private int $created = 0;

    private int $updated = 0;

    /** @var array<int, true> Set of valid department IDs (from our DB). */
    private ?array $validDepartmentIds = null;

    private ?int $defaultDepartmentId = null;

    /** القسم اختياري: إذا القيمة 0 أو فارغة نرجع null. */
    private function resolveDepartmentId(int $fromFile): ?int
    {
        if ($fromFile <= 0) {
            return null;
        }
        if ($this->validDepartmentIds === null) {
            $ids = Department::pluck('id')->all();
            $this->validDepartmentIds = array_flip($ids);
            $this->defaultDepartmentId = $ids !== [] ? (int) $ids[0] : null;
        }
        if (isset($this->validDepartmentIds[$fromFile])) {
            return $fromFile;
        }
        return $this->defaultDepartmentId;
    }

    /**
     * Import from CSV or Excel. Returns [created, updated].
     */
    public function import(string $path, string $extension): array
    {
        $this->created = 0;
        $this->updated = 0;

        $ext = strtolower($extension);
        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $this->processExcel($path, $ext);
        } else {
            $this->processCsv($path);
        }

        return [$this->created, $this->updated];
    }

    private function processCsv(string $path): void
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return;
        }

        $first = fgetcsv($handle);
        $skipFirst = $first && $this->isHeaderRow(trim((string) ($first[0] ?? '')));

        $batch = [];
        if ($first && !$skipFirst) {
            $batch[] = $this->parseCsvRow($first);
        }

        while (($row = fgetcsv($handle)) !== false) {
            $batch[] = $this->parseCsvRow($row);
            if (count($batch) >= self::BATCH_SIZE) {
                $this->processBatch($batch);
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            $this->processBatch($batch);
        }

        fclose($handle);
    }

    private function processExcel(string $path, string $ext): void
    {
        if ($ext === 'xls') {
            $this->processExcelXlsFull($path);
            return;
        }

        $chunkSize = self::BATCH_SIZE;
        $startRow = 1;
        $isFirstChunk = true;

        while (true) {
            $endRow = $startRow + $chunkSize - 1;
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $reader->setReadFilter(new ChunkReadFilter($startRow, $endRow));

            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            if ($highestRow < $startRow) {
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
                break;
            }

            $batch = [];
            $actualEnd = min($endRow, $highestRow);

            for ($row = $startRow; $row <= $actualEnd; $row++) {
                $cellA = $sheet->getCell('A' . $row)->getValue();
                $cellB = $sheet->getCell('B' . $row)->getValue();
                $cellC = $sheet->getCell('C' . $row)->getValue();
                $cellD = $sheet->getCell('D' . $row)->getValue();

                if ($isFirstChunk && $row === 1 && $this->isHeaderRow(trim((string) $cellA))) {
                    continue;
                }

                $deptFromFile = (int) $cellD;
                $batch[] = [
                    'code' => trim((string) $cellA),
                    'name' => trim((string) $cellB),
                    'price' => (float) $cellC,
                    'department_id' => $deptFromFile ? $this->resolveDepartmentId($deptFromFile) : null,
                ];
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            $this->processBatch($batch);

            if ($highestRow < $endRow) {
                break;
            }

            $startRow = $endRow + 1;
            $isFirstChunk = false;
        }
    }

    /** .xls: تحميل الملف كامل ثم معالجة كل الصفوف (ChunkReadFilter لا يعمل جيداً مع Xls). */
    private function processExcelXlsFull(string $path): void
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $batch = [];
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellA = $sheet->getCell('A' . $row)->getValue();
            $cellB = $sheet->getCell('B' . $row)->getValue();
            $cellC = $sheet->getCell('C' . $row)->getValue();
            $cellD = $sheet->getCell('D' . $row)->getValue();

            if ($row === 1 && $this->isHeaderRow(trim((string) $cellA))) {
                continue;
            }

            $deptFromFile = (int) $cellD;
            $batch[] = [
                'code' => trim((string) $cellA),
                'name' => trim((string) $cellB),
                'price' => (float) $cellC,
                'department_id' => $deptFromFile ? $this->resolveDepartmentId($deptFromFile) : null,
            ];

            if (count($batch) >= self::BATCH_SIZE) {
                $this->processBatch($batch);
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            $this->processBatch($batch);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    /**
     * @param array<int, mixed> $row
     * @return array{code: string, name: string, price: float, department_id: int}
     */
    private function parseCsvRow(array $row): array
    {
        $deptFromFile = (int) ($row[3] ?? 0);
        return [
            'code' => trim((string) ($row[0] ?? '')),
            'name' => trim((string) ($row[1] ?? '')),
            'price' => (float) ($row[2] ?? 0),
            'department_id' => $deptFromFile ? $this->resolveDepartmentId($deptFromFile) : null,
        ];
    }

    private function isHeaderRow(string $firstCell): bool
    {
        return in_array(strtolower($firstCell), self::HEADER_VALUES, true);
    }

    /**
     * @param array<int, array{code: string, name: string, price: float, department_id: int|null}> $batch
     */
    private function processBatch(array $batch): void
    {
        $toUpsert = array_filter($batch, fn (array $r) => $r['code'] !== '');

        if (empty($toUpsert)) {
            return;
        }

        DB::transaction(function () use ($toUpsert) {
            foreach ($toUpsert as $row) {
                $svc = Service::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'name' => $row['name'],
                        'name_ar' => $row['name'],
                        'default_price' => $row['price'],
                        'department_id' => $row['department_id'],
                        'is_active' => true,
                    ]
                );
                $svc->wasRecentlyCreated ? $this->created++ : $this->updated++;
            }
        });
    }
}
