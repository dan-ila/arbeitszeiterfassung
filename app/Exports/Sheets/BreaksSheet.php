<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BreaksSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(
        protected $user,
        protected int $month,
        protected int $year,
        /** @var \Illuminate\Support\Collection<int,\App\Models\WorkLog> */
        protected Collection $worklogs,
    ) {
    }

    public function title(): string
    {
        return 'Pausen';
    }

    public function headings(): array
    {
        return [
            'Datum',
            'Start',
            'Ende',
            'Minuten',
            'Notiz',
            'Schicht',
            'WorkLog ID',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->worklogs->sortBy('clock_in') as $log) {
            $clockIn = Carbon::parse($log->clock_in);
            $clockOut = $log->clock_out ? Carbon::parse($log->clock_out) : null;
            $shiftLabel = $clockIn->format('H:i').'-'.($clockOut ? $clockOut->format('H:i') : 'offen');

            foreach (collect($log->workBreak ?? [])->sortBy('start_time') as $br) {
                if (empty($br->start_time) || empty($br->end_time)) {
                    continue;
                }

                $brStart = Carbon::parse($br->start_time);
                $brEnd = Carbon::parse($br->end_time);
                $minutes = $brEnd->diffInMinutes($brStart);

                $rows[] = [
                    $clockIn->format('d.m.Y'),
                    $brStart->format('H:i'),
                    $brEnd->format('H:i'),
                    $minutes,
                    (string) ($br->note ?? ''),
                    $shiftLabel,
                    (int) $log->id,
                ];
            }
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 10,
            'C' => 10,
            'D' => 10,
            'E' => 28,
            'F' => 14,
            'G' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF005461']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(20);

        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A2:{$highestColumn}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("D2:D{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$highestColumn}1");
                $sheet->setSelectedCell('A1');
            },
        ];
    }
}
