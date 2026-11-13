<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PilketosTeacherTokenExport implements FromCollection, WithHeadings
{
    public function __construct(private array $rows) {}

    public function headings(): array
    {
        return ['No', 'Nama', 'Username', 'Role', 'Token', 'Status'];
    }

    public function collection(): Collection
    {
        return collect($this->rows)->map(fn (array $row) => [$row['no'], $this->safe($row['name']), $this->safe($row['username']), $this->safe($row['roles']), $this->safe($row['token'] ?? '-'), $row['status']]);
    }

    private function safe(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) ? "'{$value}" : $value;
    }
}
