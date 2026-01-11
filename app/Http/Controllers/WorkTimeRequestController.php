<?php

namespace App\Http\Controllers;

use App\Mail\AdminNewWorkTimeRequestMail;
use App\Models\WorkLog;
use App\Models\WorkTimeRequest;
use App\Models\WorkBreak;
use App\Models\User;
use App\Support\Holidays\HessenHolidays;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WorkTimeRequestController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = Carbon::createFromFormat('Y-m-d', $request->string('date'));

        if (HessenHolidays::isBlockedForAdd($date)) {
            $holidayName = HessenHolidays::holidayName($date);
            $msg = $holidayName
                ? "An diesem Tag (Feiertag: {$holidayName}) kann keine Arbeitszeit hinzugefügt werden."
                : 'An Wochenenden kann keine Arbeitszeit hinzugefügt werden.';

            return redirect()->route('users.dashboard', [
                'year' => $date->year,
                'month' => $date->month,
            ])->with('error', $msg);
        }

        $existingLogCount = WorkLog::where('user_id', Auth::id())
            ->whereDate('clock_in', $date->toDateString())
            ->count();

        return view('users.worktimeRequests.form', [
            'mode' => 'add',
            'date' => $date,
            'workLog' => null,
            'existingLogCount' => $existingLogCount,
            'breakMinutes' => 0,
        ]);
    }

    public function edit(WorkLog $workLog)
    {
        if ($workLog->user_id !== Auth::id()) {
            abort(403);
        }

        $breakMinutes = (int) WorkBreak::where('work_log_id', $workLog->id)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->get()
            ->sum(function ($break) {
                return Carbon::parse($break->start_time)
                    ->diffInMinutes(Carbon::parse($break->end_time));
            });

        return view('users.worktimeRequests.form', [
            'mode' => 'edit',
            'date' => Carbon::parse($workLog->clock_in),
            'workLog' => $workLog,
            'existingLogCount' => null,
            'breakMinutes' => $breakMinutes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:add,edit',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_minutes' => 'required|integer|min:0|max:600',
            'reason' => 'nullable|string|max:2000',
            'work_log_id' => 'nullable|integer',
        ]);

        $mode = $request->string('mode');
        $date = Carbon::createFromFormat('Y-m-d', $request->string('date'));

        if ($mode->toString() === 'add' && HessenHolidays::isBlockedForAdd($date)) {
            $holidayName = HessenHolidays::holidayName($date);
            $msg = $holidayName
                ? "An diesem Tag (Feiertag: {$holidayName}) kann keine Arbeitszeit hinzugefügt werden."
                : 'An Wochenenden kann keine Arbeitszeit hinzugefügt werden.';

            return back()->withErrors(['date' => $msg])->withInput();
        }

        $requestedClockIn = Carbon::createFromFormat('Y-m-d H:i', $date->toDateString().' '.$request->string('start_time'));
        $requestedClockOut = Carbon::createFromFormat('Y-m-d H:i', $date->toDateString().' '.$request->string('end_time'));

        $requestedBreakMinutes = (int) $request->input('break_minutes');
        $requestedWorkMinutes = $requestedClockOut->diffInMinutes($requestedClockIn);

        // Rule: if working more than 6 hours, break must be 30 minutes or longer
        if ($requestedWorkMinutes > 6 * 60 && $requestedBreakMinutes < 30) {
            return back()->withErrors([
                'break_minutes' => 'Bei mehr als 6 Stunden Arbeitszeit muss die Pause mindestens 30 Minuten betragen.',
            ])->withInput();
        }

        $workLogId = $request->input('work_log_id');
        $workLog = null;

        if ($mode->toString() === 'edit') {
            if (!$workLogId) {
                return back()->withErrors(['work_log_id' => 'Ungültiger Arbeitszeiteintrag.'])->withInput();
            }

            $workLog = WorkLog::findOrFail($workLogId);
            if ($workLog->user_id !== Auth::id()) {
                abort(403);
            }

            $alreadyPending = WorkTimeRequest::where('work_log_id', $workLog->id)
                ->where('status', 'pending')
                ->exists();

            if ($alreadyPending) {
                return back()->withErrors(['work_log_id' => 'Für diesen Eintrag gibt es bereits eine offene Anfrage.'])->withInput();
            }
        } else {
            // Split shifts: allow multiple entries per day, but do not allow overlaps.
            $logsThatDay = WorkLog::where('user_id', Auth::id())
                ->whereDate('clock_in', $date->toDateString())
                ->get();

            $hasOpenShift = $logsThatDay->contains(fn ($l) => $l->clock_out === null);
            if ($hasOpenShift) {
                return back()->withErrors(['date' => 'Für diesen Tag gibt es eine offene Schicht (ohne Endzeit). Bitte zuerst abschließen oder „Änderung beantragen“.'])->withInput();
            }

            foreach ($logsThatDay as $existing) {
                if (!$existing->clock_out) {
                    continue;
                }
                $existingStart = Carbon::parse($existing->clock_in);
                $existingEnd = Carbon::parse($existing->clock_out);

                $overlaps = $requestedClockIn->lt($existingEnd) && $requestedClockOut->gt($existingStart);
                if ($overlaps) {
                    return back()->withErrors(['start_time' => 'Die gewünschte Schicht überschneidet sich mit einer bestehenden Arbeitszeit.'])->withInput();
                }
            }

            $alreadyPending = WorkTimeRequest::where('user_id', Auth::id())
                ->where('type', 'add')
                ->where('status', 'pending')
                ->whereDate('requested_clock_in', $date->toDateString())
                ->get();

            foreach ($alreadyPending as $pending) {
                $pendingStart = Carbon::parse($pending->requested_clock_in);
                $pendingEnd = Carbon::parse($pending->requested_clock_out);
                $overlaps = $requestedClockIn->lt($pendingEnd) && $requestedClockOut->gt($pendingStart);
                if ($overlaps) {
                    return back()->withErrors(['start_time' => 'Es gibt bereits eine offene Anfrage, die sich mit dieser Schicht überschneidet.'])->withInput();
                }
            }
        }

        $created = WorkTimeRequest::create([
            'user_id' => Auth::id(),
            'work_log_id' => $workLog?->id,
            'type' => $mode->toString(),
            'status' => 'pending',
            'requested_clock_in' => $requestedClockIn,
            'requested_clock_out' => $requestedClockOut,
            'requested_break_minutes' => $requestedBreakMinutes,
            'reason' => $request->input('reason'),
        ]);

        $adminEmails = User::query()
            ->where('role', 'admin')
            ->pluck('email')
            ->filter()
            ->values()
            ->all();

        if (!empty($adminEmails)) {
            $created->loadMissing('user');
            Mail::to($adminEmails)->send(new AdminNewWorkTimeRequestMail($created));
        }

        return redirect()->route('users.dashboard', [
            'year' => $date->year,
            'month' => $date->month,
        ])->with('success', 'Anfrage wurde erstellt und wartet auf Freigabe.');
    }
}
