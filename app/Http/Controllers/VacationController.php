<?php

namespace App\Http\Controllers;

use App\Mail\AdminNewVacationRequestMail;
use App\Models\Vacation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class VacationController extends Controller
{
    protected $annualDays = 30;

    /**
     * Show user's vacations and remaining days for the current year.
     */
    public function index()
    {
        $user = Auth::user();
        $year = (int) request()->query('year', now()->year);
        $status = request()->query('status');
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = null;
        }

        // Get all vacations that overlap with the current year
        $vacationsQuery = $user->vacations()
            ->where(function ($query) use ($year) {
                $query->whereYear('start_date', $year)
                      ->orWhereYear('end_date', $year);
            })
            ->orderBy('start_date');

        if ($status) {
            $vacationsQuery->where('status', $status);
        }

        $vacations = $vacationsQuery->get();

        // Define holidays for the current year
        $holidays = [
            Carbon::create($year, 1, 1),
            Carbon::create($year, 5, 1),
            Carbon::create($year, 10, 3),
            Carbon::create($year, 12, 25),
            Carbon::create($year, 12, 26),
        ];

        // Calculate used days for the current year (clip to year)
        $usedDays = 0;
        foreach ($vacations as $v) {
            $vStart = Carbon::parse($v->start_date)->copy()->max(Carbon::create($year, 1, 1));
            $vEnd   = Carbon::parse($v->end_date)->copy()->min(Carbon::create($year, 12, 31));
            $usedDays += $this->countBusinessDays($vStart, $vEnd, $holidays);
        }

        $remainingDays = $this->annualDays - $usedDays;

        return view('users.vacation.index', compact('vacations', 'remainingDays', 'usedDays', 'year', 'status'));
    }

    public function create()
    {
        return view('users.vacation.create');
    }

    /**
     * Store a new vacation request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $user = Auth::user();
        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);

        // Calculate requested business days
        $requestedDays = $this->calculateBusinessDaysForVacation($start, $end);

        // Calculate already used vacation days for the current year
        $usedDays = $this->calculateUsedDaysForYear($user, $start->year);

        $remainingDays = $this->annualDays - $usedDays;

        if ($requestedDays > $remainingDays) {
            return back()
                ->withErrors(['end_date' => 'Du hast nicht genug Urlaubstage.'])
                ->withInput();
        }

        $vacation = Vacation::create([
            'user_id'    => $user->id,
            'start_date' => $start->format('Y-m-d'),
            'end_date'   => $end->format('Y-m-d'),
            'days'       => $requestedDays,
            'status'     => 'pending',
        ]);

        $adminEmails = User::query()
            ->where('role', 'admin')
            ->pluck('email')
            ->filter()
            ->values()
            ->all();

        if (!empty($adminEmails)) {
            $vacation->loadMissing('user');
            Mail::to($adminEmails)->send(new AdminNewVacationRequestMail($vacation));
        }

        return redirect()->route('users.vacation')
            ->with('success', "Urlaub beantragt ($requestedDays Tage).");
    }

    /**
     * Calculate business days for a vacation spanning multiple years.
     */
    private function calculateBusinessDaysForVacation(Carbon $start, Carbon $end): int
    {
        $days = 0;

        for ($year = $start->year; $year <= $end->year; $year++) {
            $yearStart = Carbon::create($year, 1, 1);
            $yearEnd   = Carbon::create($year, 12, 31);

            $clipStart = $start->copy()->max($yearStart);
            $clipEnd   = $end->copy()->min($yearEnd);

            $holidays = [
                Carbon::create($year, 1, 1),
                Carbon::create($year, 5, 1),
                Carbon::create($year, 10, 3),
                Carbon::create($year, 12, 25),
                Carbon::create($year, 12, 26),
            ];

            $days += $this->countBusinessDays($clipStart, $clipEnd, $holidays);
        }

        return $days;
    }

    /**
     * Calculate used vacation days for the current year
     */
    private function calculateUsedDaysForYear($user, int $year): int
    {
        $vacations = $user->vacations()
            ->where(function ($query) use ($year) {
                $query->whereYear('start_date', $year)
                      ->orWhereYear('end_date', $year);
            })
            ->get();

        $holidays = [
            Carbon::create($year, 1, 1),
            Carbon::create($year, 5, 1),
            Carbon::create($year, 10, 3),
            Carbon::create($year, 12, 25),
            Carbon::create($year, 12, 26),
        ];

        $usedDays = 0;
        foreach ($vacations as $v) {
            $vStart = Carbon::parse($v->start_date)->copy()->max(Carbon::create($year, 1, 1));
            $vEnd   = Carbon::parse($v->end_date)->copy()->min(Carbon::create($year, 12, 31));
            $usedDays += $this->countBusinessDays($vStart, $vEnd, $holidays);
        }

        return $usedDays;
    }

    /**
     * Count business days between two dates (inclusive), excluding holidays
     */
    private function countBusinessDays(Carbon $start, Carbon $end, array $holidays = []): int
    {
        if ($start->gt($end)) return 0;

        $days = 0;
        foreach (CarbonPeriod::create($start, $end) as $date) {
            if (!$date->isWeekend() && !$this->isHoliday($date, $holidays)) {
                $days++;
            }
        }

        return $days;
    }

    /**
     * Check if a date is a holiday
     */
    private function isHoliday(Carbon $date, array $holidays): bool
    {
        foreach ($holidays as $holiday) {
            if ($date->isSameDay($holiday)) return true;
        }
        return false;
    }
}
