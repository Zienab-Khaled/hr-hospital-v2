<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/** Read only rows in the given range (for memory-efficient Excel import). */
class ChunkReadFilter implements IReadFilter
{
    public function __construct(
        private int $startRow,
        private int $endRow
    ) {}

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row <= $this->endRow;
    }
}
