<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkBreak;
use App\Models\WorkLog;
use App\Support\Holidays\HessenHolidays;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    
public function index(Request $request)
{
    $currentUser = Auth::user();

    // Selected month/year (fallback = current)
    $year  = $request->get('year', now()->year);
    $month = $request->get('month', now()->month);

    $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
    $endOfMonth   = Carbon::create($year, $month, 1)->endOfMonth();

    $usersCount = User::count();

    $activeUsers = WorkLog::where('user_id', $currentUser->id)
        ->whereNull('clock_out')
        ->count();

    $currentDayOfWeekAsStringInGerman = [
        'Sunday' => 'Sonntag',
        'Monday' => 'Montag',
        'Tuesday' => 'Dienstag',
        'Wednesday' => 'Mittwoch',
        'Thursday' => 'Donnerstag',
        'Friday' => 'Freitag',
        'Saturday' => 'Samstag',
    ];

    $currentDayOfWeek = $currentDayOfWeekAsStringInGerman[now()->format('l')];

    /*
     |--------------------------------------------------------------------------
     | ✅ Worked hours FOR SELECTED MONTH
     |--------------------------------------------------------------------------
     */
    $workedHours = WorkLog::where('user_id', $currentUser->id)
        ->whereNotNull('clock_out')
        ->whereBetween('clock_in', [$startOfMonth, $endOfMonth])
        ->get()
        ->sum(function ($log) {
            $clockIn  = Carbon::parse($log->clock_in);
            $clockOut = Carbon::parse($log->clock_out);

            return $clockOut->diffInMinutes($clockIn) / 60;
        });

    // Safety: prevent negative display values (e.g. inconsistent timestamps)
    $workedHours = round(abs($workedHours), 1);

    /*
     |--------------------------------------------------------------------------
     | Worklogs for selected month
     |--------------------------------------------------------------------------
     */
    $worklogs = WorkLog::where('user_id', $currentUser->id)
        ->whereBetween('clock_in', [$startOfMonth, $endOfMonth])
        ->with('workBreak')
        ->get();

    /*
     |--------------------------------------------------------------------------
     | Break durations
     |--------------------------------------------------------------------------
     */
    $breakDurations = [];

    foreach ($worklogs as $log) {
        $totalBreakMinutes = WorkBreak::where('work_log_id', $log->id)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->get()
            ->sum(function ($break) {
                return Carbon::parse($break->start_time)
                    ->diffInMinutes(Carbon::parse($break->end_time));
            });

        // ✅ force integer minutes
        $breakDurations[$log->id] = (int) $totalBreakMinutes;
    }

    // Calendar meta for UI (holidays/weekends) so users can't add time there
    $holidayMap = HessenHolidays::holidaysForYear((int) $year);
    $holidaysInMonth = [];
    foreach ($holidayMap as $date => $name) {
        $d = Carbon::createFromFormat('Y-m-d', $date);
        if ((int) $d->month === (int) $month) {
            $holidaysInMonth[$date] = $name;
        }
    }

    // Used by dashboard UX: hide "Schicht hinzufügen" if there is already a shift after a break.
    // A day is considered "has shift after break" if the latest break end is before another shift's start.
    $hasShiftAfterBreak = [];
    $logsByDay = $worklogs->groupBy(function ($log) {
        return Carbon::parse($log->clock_in)->format('Y-m-d');
    });

    foreach ($logsByDay as $date => $logs) {
        $logsSorted = $logs->sortBy('clock_in')->values();

        $latestBreakEnd = null;
        foreach ($logsSorted as $log) {
            foreach (($log->workBreak ?? []) as $br) {
                if (!$br->start_time || !$br->end_time) {
                    continue;
                }
                $end = Carbon::parse($br->end_time);
                if (!$latestBreakEnd || $end->gt($latestBreakEnd)) {
                    $latestBreakEnd = $end;
                }
            }
        }

        $flag = false;
        if ($latestBreakEnd) {
            foreach ($logsSorted as $log) {
                $start = Carbon::parse($log->clock_in);
                if ($start->gt($latestBreakEnd)) {
                    $flag = true;
                    break;
                }
            }
        }

        $hasShiftAfterBreak[$date] = $flag;
    }

    return view('users.dashboard', compact(
        'usersCount',
        'activeUsers',
        'workedHours',
        'currentDayOfWeek',
        'worklogs',
        'breakDurations',
        'hasShiftAfterBreak',
        'holidaysInMonth',
        'year',
        'month'
    ));
}

}
