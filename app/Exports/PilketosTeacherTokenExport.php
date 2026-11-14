<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PilketosTeacherTokenExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    public function __construct(private array $rows) {}

    public function headings(): array
    {
        return ['No', 'Nama', 'Username', 'Role', 'Token', 'Status'];
    }

    public function collection(): Collection
    {
        return collect($this->rows)->map(fn (array $row): array => [
            $row['no'],
            $this->safe($row['name']),
            $this->safe($row['username']),
            $this->safe($row['roles']),
            $this->safe($row['token_label']),
            $this->safe($row['status']),
        ]);
    }

    public function columnFormats(): array
    {
        return ['E' => NumberFormat::FORMAT_TEXT];
    }

    public function columnWidths(): array
    {
        return ['A' => 8, 'B' => 32, 'C' => 22, 'D' => 28, 'E' => 24, 'F' => 16];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A:F')->getAlignment()->setVertical('center')->setWrapText(true);

        return [1 => ['fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'D1FAE5']]]];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            $sheet = $event->sheet->getDelegate();
            $lastRow = max(1, count($this->rows) + 1);
            $sheet->freezePane('A2');
            $sheet->getStyle("A1:F{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }];
    }

    private function safe(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) ? "'{$value}" : $value;
    }
}
