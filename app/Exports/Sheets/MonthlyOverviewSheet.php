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

class MonthlyOverviewSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths, WithEvents
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
        return 'Monatsübersicht';
    }

    public function headings(): array
    {
        return [
            'Datum',
            'Wochentag',
            'Erste Buchung',
            'Letzte Buchung',
            'Schichten',
            'Pause (Min)',
            'Arbeitszeit (hh:mm)',
            'Hinweis',
        ];
    }

    public function array(): array
    {
        $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
        $rows = [];

        $totalBreakMinutes = 0;
        $totalWorkMinutes = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateObj = Carbon::create($this->year, $this->month, $day);
            $dateStr = $dateObj->toDateString();
            $weekday = ucfirst($dateObj->locale('de')->isoFormat('dddd'));

            $logsForDay = $this->worklogs
                ->filter(fn ($l) => Carbon::parse($l->clock_in)->toDateString() === $dateStr)
                ->sortBy('clock_in')
                ->values();

            $shiftCount = $logsForDay->count();
            $hasOpenShift = (bool) $logsForDay->first(fn ($l) => is_null($l->clock_out));

            $firstIn = $shiftCount > 0
                ? Carbon::parse($logsForDay->first()->clock_in)->format('H:i')
                : '';

            $lastOutModel = $logsForDay
                ->filter(fn ($l) => !is_null($l->clock_out))
                ->sortByDesc('clock_out')
                ->first();

            $lastOut = $lastOutModel
                ? Carbon::parse($lastOutModel->clock_out)->format('H:i')
                : ($hasOpenShift ? 'offen' : '');

            $breakMinutes = (int) $logsForDay->sum(function ($l) {
                return (int) collect($l->workBreak ?? [])->sum(function ($br) {
                    if (empty($br->start_time) || empty($br->end_time)) {
                        return 0;
                    }

                    return Carbon::parse($br->end_time)
                        ->diffInMinutes(Carbon::parse($br->start_time));
                });
            });

            $workMinutes = (int) $logsForDay->sum(function ($l) {
                if (!$l->clock_out) {
                    return 0;
                }

                $gross = Carbon::parse($l->clock_out)->diffInMinutes(Carbon::parse($l->clock_in));
                $breaks = (int) collect($l->workBreak ?? [])->sum(function ($br) {
                    if (empty($br->start_time) || empty($br->end_time)) {
                        return 0;
                    }

                    return Carbon::parse($br->end_time)
                        ->diffInMinutes(Carbon::parse($br->start_time));
                });

                return max(0, $gross - $breaks);
            });

            $totalBreakMinutes += $breakMinutes;
            $totalWorkMinutes += $workMinutes;

            $rows[] = [
                $dateObj->format('d.m.Y'),
                $weekday,
                $firstIn,
                $lastOut,
                $shiftCount > 0 ? $shiftCount : '',
                $shiftCount > 0 ? $breakMinutes : '',
                $shiftCount > 0 ? $this->formatMinutes($workMinutes) : '',
                $hasOpenShift ? 'Offene Schicht' : '',
            ];
        }

        // Totals row
        $rows[] = [
            '',
            'Summe',
            '',
            '',
            '',
            $totalBreakMinutes,
            $this->formatMinutes($totalWorkMinutes),
            '',
        ];

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 14,
            'C' => 14,
            'D' => 14,
            'E' => 10,
            'F' => 12,
            'G' => 18,
            'H' => 18,
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

        // Grid
        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Totals row
        $sheet->getStyle("A{$highestRow}:{$highestColumn}{$highestRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF4F4F4']],
        ]);

        $sheet->getStyle("A2:{$highestColumn}{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("E2:G{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$highestColumn}1");
                $sheet->setSelectedCell('A1');
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->getAlignment()->setWrapText(false);
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
