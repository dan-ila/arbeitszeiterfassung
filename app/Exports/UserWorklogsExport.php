<?php

namespace App\Exports;

use App\Models\WorkLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;
use App\Exports\Sheets\MonthlyOverviewSheet;
use App\Exports\Sheets\ShiftsSheet;
use App\Exports\Sheets\BreaksSheet;

class UserWorklogsExport implements WithMultipleSheets, WithProperties
{
    protected $user;
    protected $month;
    protected $year;

    /** @var \Illuminate\Support\Collection<int,\App\Models\WorkLog> */
    protected Collection $worklogs;

    public function __construct($user, $month, $year)
    {
        $this->user  = $user;
        $this->month = (int) $month;
        $this->year  = (int) $year;

        $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end   = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        // Preload all worklogs + breaks for the month (supports multiple shifts per day)
        $this->worklogs = WorkLog::query()
            ->with(['workBreak' => function ($q) {
                $q->whereNotNull('start_time')->whereNotNull('end_time');
            }])
            ->where('user_id', $this->user->id)
            ->whereBetween('clock_in', [$start, $end])
            ->orderBy('clock_in')
            ->get();
    }

    public function properties(): array
    {
        $monthLabel = Carbon::create($this->year, $this->month, 1)->locale('de')->isoFormat('MMMM YYYY');

        return [
            'title' => 'Arbeitszeiten Export',
            'subject' => 'Arbeitszeiten',
            'creator' => config('app.name'),
            'company' => config('app.name'),
            'description' => "Arbeitszeiten für {$this->user->first_name} {$this->user->last_name} ({$monthLabel})",
        ];
    }

    public function sheets(): array
    {
        return [
            new MonthlyOverviewSheet($this->user, $this->month, $this->year, $this->worklogs),
            new ShiftsSheet($this->user, $this->month, $this->year, $this->worklogs),
            new BreaksSheet($this->user, $this->month, $this->year, $this->worklogs),
        ];
    }
}
