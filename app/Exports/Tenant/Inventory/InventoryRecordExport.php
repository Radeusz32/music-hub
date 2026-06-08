<?php

declare(strict_types=1);

namespace App\Exports\Tenant\Inventory;

use App\Enums\Tenant\DiscConditionEnum;
use App\Enums\Tenant\DiscFormatEnum;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

final class InventoryRecordExport implements WithMultipleSheets
{
    /** @return list<InventoryRecordImportSheet|InventoryRecordDictionarySheet> */
    public function sheets(): array
    {
        return [
            new InventoryRecordImportSheet(),
            new InventoryRecordDictionarySheet(),
        ];
    }
}

final class InventoryRecordImportSheet implements ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    /** @return list<string> */
    public function headings(): array
    {
        return [
            'Tytuł *',
            'Wykonawca *',
            'Gatunek *',
            'Format *',
            'Stan *',
            'Wytwórnia',
            'Nr katalogowy',
            'Kod kreskowy',
            'Kraj',
            'Rok',
            'Ilość *',
            'Cena zakupu',
            'Cena sprzedaży',
            'Notatki',
        ];
    }

    public function title(): string
    {
        return 'Import';
    }

    /** @return array<string, callable> */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->getRowDimension(1)->setRowHeight(24);

                $requiredStyle = [
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ];

                $optionalStyle = [
                    'font' => ['bold' => false, 'color' => ['argb' => 'FF94A3B8'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ];

                foreach (['A', 'B', 'C', 'D', 'E', 'K'] as $col) {
                    $sheet->getStyle("{$col}1")->applyFromArray($requiredStyle);
                }

                foreach (['F', 'G', 'H', 'I', 'J', 'L', 'M', 'N'] as $col) {
                    $sheet->getStyle("{$col}1")->applyFromArray($optionalStyle);
                }

                $formats = implode(', ', array_column(DiscFormatEnum::cases(), 'value'));
                $conditions = implode(', ', array_column(DiscConditionEnum::cases(), 'value'));

                $sheet->getComment('D1')
                    ->getText()->createTextRun("Dozwolone wartości:\n{$formats}\n(patrz arkusz 'Słownik wartości')");

                $sheet->getComment('E1')
                    ->getText()->createTextRun("Dozwolone wartości:\n{$conditions}\n(patrz arkusz 'Słownik wartości')");
            },
        ];
    }
}

final class InventoryRecordDictionarySheet implements ShouldAutoSize, WithEvents, WithTitle
{
    public function title(): string
    {
        return 'Słownik wartości';
    }

    /** @return array<string, callable> */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ];

                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->mergeCells('A1:C1');
                $sheet->mergeCells('E1:G1');
                $sheet->setCellValue('A1', 'Format — dozwolone wartości');
                $sheet->setCellValue('E1', 'Stan — dozwolone wartości');
                $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
                $sheet->getStyle('E1:G1')->applyFromArray($headerStyle);

                $row = 2;

                foreach (DiscFormatEnum::cases() as $case) {
                    $sheet->setCellValue("A{$row}", $case->value);
                    $sheet->setCellValue("B{$row}", $case->label());
                    $row++;
                }

                $row = 2;

                foreach (DiscConditionEnum::cases() as $case) {
                    $sheet->setCellValue("E{$row}", $case->value);
                    $sheet->setCellValue("F{$row}", $case->label());
                    $row++;
                }
            },
        ];
    }
}
