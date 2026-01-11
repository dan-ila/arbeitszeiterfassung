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

class ShiftsSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
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
        return 'Schichten';
    }

    public function headings(): array
    {
        return [
            'Datum',
            'Start',
            'Ende',
            'Dauer (Min)',
            'Pause (Min)',
            'Netto (hh:mm)',
            'Quelle',
            'WorkLog ID',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->worklogs->sortBy('clock_in') as $log) {
            $clockIn = Carbon::parse($log->clock_in);
            $clockOut = $log->clock_out ? Carbon::parse($log->clock_out) : null;

            $breakMinutes = (int) collect($log->workBreak ?? [])->sum(function ($br) {
                if (empty($br->start_time) || empty($br->end_time)) {
                    return 0;
                }

                return Carbon::parse($br->end_time)
                    ->diffInMinutes(Carbon::parse($br->start_time));
            });

            $gross = $clockOut ? $clockOut->diffInMinutes($clockIn) : 0;
            $net = max(0, $gross - $breakMinutes);

            $rows[] = [
                $clockIn->format('d.m.Y'),
                $clockIn->format('H:i'),
                $clockOut ? $clockOut->format('H:i') : 'offen',
                $clockOut ? $gross : '',
                $clockOut ? $breakMinutes : '',
                $clockOut ? $this->formatMinutes($net) : '',
                (string) ($log->source ?? 'terminal'),
                (int) $log->id,
            ];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 10,
            'C' => 10,
            'D' => 12,
            'E' => 12,
            'F' => 14,
            'G' => 12,
            'H' => 12,
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
        $sheet->getStyle("D2:F{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
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

    private function formatMinutes(int $minutes): string
    {
        $minutes = max(0, $minutes);
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return sprintf('%02d:%02d', $h, $m);
    }
}
