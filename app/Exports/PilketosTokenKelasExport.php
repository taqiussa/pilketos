<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PilketosTokenKelasExport implements FromCollection, ShouldAutoSize, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    /** @param list<array{no: int, name: string, kelas: string, token: ?string}> $rows */
    public function __construct(private array $rows) {}

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return ['No', 'Nama Siswa', 'Kelas', 'Token'];
    }

    public function map($row): array
    {
        return [$row['no'], $this->safeText($row['name']), $this->safeText($row['kelas']), $this->safeText($row['token'] ?? '-')];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A:D')->getAlignment()->setVertical('center');
        $sheet->getStyle('D:D')->getNumberFormat()->setFormatCode('@');

        return [1 => ['fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'D1FAE5']]]];
    }

    public function columnWidths(): array
    {
        return ['A' => 8, 'B' => 32, 'C' => 14, 'D' => 16];
    }

    private function safeText(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) ? "'{$value}" : $value;
    }
}
