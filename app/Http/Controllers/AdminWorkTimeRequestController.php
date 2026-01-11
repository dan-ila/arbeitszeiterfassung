<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use App\Models\WorkTimeRequest;
use App\Models\WorkBreak;
use App\Models\Vacation;
use App\Mail\WorkTimeRequestStatusMail;
use App\Support\Holidays\HessenHolidays;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AdminWorkTimeRequestController extends Controller
{
    private function redirectToIndex()
    {
        return redirect()->route('admin.worktime.requests.index');
    }

    public function index(Request $request)
    {
        $pendingWorkCount = WorkTimeRequest::where('status', 'pending')->count();
        $pendingVacationCount = Vacation::where('status', 'pending')->count();

        $workSearch = trim((string) $request->query('work_search', ''));
        $workStatus = $request->query('work_status');
        $workType = $request->query('work_type');
        $workFrom = $request->query('work_from');
        $workTo = $request->query('work_to');

        $requestsQuery = WorkTimeRequest::with(['user', 'workLog']);

        if ($workSearch !== '') {
            $requestsQuery->where(function ($q) use ($workSearch) {
                $q->where('reason', 'like', "%{$workSearch}%")
                    ->orWhereHas('user', function ($uq) use ($workSearch) {
                        $uq->where('first_name', 'like', "%{$workSearch}%")
                            ->orWhere('last_name', 'like', "%{$workSearch}%")
                            ->orWhere('email', 'like', "%{$workSearch}%");
                    });
            });
        }

        if (in_array($workStatus, ['pending', 'approved', 'rejected'], true)) {
            $requestsQuery->where('status', $workStatus);
        } else {
            $workStatus = null;
        }

        if (in_array($workType, ['add', 'edit'], true)) {
            $requestsQuery->where('type', $workType);
        } else {
            $workType = null;
        }

        if (is_string($workFrom) && $workFrom !== '') {
            $requestsQuery->whereDate('requested_clock_in', '>=', $workFrom);
        } else {
            $workFrom = null;
        }

        if (is_string($workTo) && $workTo !== '') {
            $requestsQuery->whereDate('requested_clock_in', '<=', $workTo);
        } else {
            $workTo = null;
        }

        $requests = $requestsQuery
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();

        $vacSearch = trim((string) $request->query('vac_search', ''));
        $vacStatus = $request->query('vac_status');
        $vacFrom = $request->query('vac_from');
        $vacTo = $request->query('vac_to');

        $vacationQuery = Vacation::with('user');

        if ($vacSearch !== '') {
            $vacationQuery->whereHas('user', function ($uq) use ($vacSearch) {
                $uq->where('first_name', 'like', "%{$vacSearch}%")
                    ->orWhere('last_name', 'like', "%{$vacSearch}%")
                    ->orWhere('email', 'like', "%{$vacSearch}%");
            });
        }

        if (in_array($vacStatus, ['pending', 'approved', 'rejected'], true)) {
            $vacationQuery->where('status', $vacStatus);
        } else {
            $vacStatus = null;
        }

        if (is_string($vacFrom) && $vacFrom !== '') {
            $vacationQuery->whereDate('start_date', '>=', $vacFrom);
        } else {
            $vacFrom = null;
        }

        if (is_string($vacTo) && $vacTo !== '') {
            $vacationQuery->whereDate('end_date', '<=', $vacTo);
        } else {
            $vacTo = null;
        }

        $vacationRequests = $vacationQuery
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();

        return view('admins.worktimeRequests.index', compact(
            'requests',
            'vacationRequests',
            'pendingWorkCount',
            'pendingVacationCount',
            'workSearch',
            'workStatus',
            'workType',
            'workFrom',
            'workTo',
            'vacSearch',
            'vacStatus',
            'vacFrom',
            'vacTo'
        ));
    }

    public function approve(WorkTimeRequest $workTimeRequest)
    {
        if ($workTimeRequest->status !== 'pending') {
            return $this->redirectToIndex()->with('error', 'Diese Anfrage wurde bereits bearbeitet.');
        }

        $logToUpdateBreak = null;

        if ($workTimeRequest->type === 'add') {
            $requestedDate = Carbon::parse($workTimeRequest->requested_clock_in);
            if (HessenHolidays::isBlockedForAdd($requestedDate)) {
                $holidayName = HessenHolidays::holidayName($requestedDate);
                $msg = $holidayName
                    ? "An diesem Tag (Feiertag: {$holidayName}) darf keine Arbeitszeit hinzugefügt werden."
                    : 'An Wochenenden darf keine Arbeitszeit hinzugefügt werden.';

                return $this->redirectToIndex()->with('error', $msg);
            }

            $logsThatDay = WorkLog::where('user_id', $workTimeRequest->user_id)
                ->whereDate('clock_in', $requestedDate->toDateString())
                ->get();

            $hasOpenShift = $logsThatDay->contains(fn ($l) => $l->clock_out === null);
            if ($hasOpenShift) {
                return $this->redirectToIndex()->with('error', 'Für diesen Tag gibt es eine offene Schicht (ohne Endzeit).');
            }

            $reqStart = Carbon::parse($workTimeRequest->requested_clock_in);
            $reqEnd = Carbon::parse($workTimeRequest->requested_clock_out);

            foreach ($logsThatDay as $existing) {
                if (!$existing->clock_out) {
                    continue;
                }
                $existingStart = Carbon::parse($existing->clock_in);
                $existingEnd = Carbon::parse($existing->clock_out);
                $overlaps = $reqStart->lt($existingEnd) && $reqEnd->gt($existingStart);
                if ($overlaps) {
                    return $this->redirectToIndex()->with('error', 'Die gewünschte Schicht überschneidet sich mit einer bestehenden Arbeitszeit.');
                }
            }

            $logToUpdateBreak = WorkLog::create([
                'user_id' => $workTimeRequest->user_id,
                'clock_in' => $workTimeRequest->requested_clock_in,
                'clock_out' => $workTimeRequest->requested_clock_out,
                'source' => 'web',
            ]);
        } else {
            if (!$workTimeRequest->work_log_id || !$workTimeRequest->workLog) {
                return $this->redirectToIndex()->with('error', 'Der referenzierte Arbeitszeiteintrag existiert nicht mehr.');
            }

            $workTimeRequest->workLog->update([
                'clock_in' => $workTimeRequest->requested_clock_in,
                'clock_out' => $workTimeRequest->requested_clock_out,
            ]);

            $logToUpdateBreak = $workTimeRequest->workLog;
        }

        // Apply requested break minutes by creating a single break block (deterministic placement)
        if ($logToUpdateBreak) {
            $minutes = (int) ($workTimeRequest->requested_break_minutes ?? 0);
            $logToUpdateBreak->workBreak()->delete();

            if ($minutes > 0) {
                $clockIn = Carbon::parse($logToUpdateBreak->clock_in);
                $clockOut = Carbon::parse($logToUpdateBreak->clock_out);
                $workMinutes = $clockOut->diffInMinutes($clockIn);

                // Place break centered in the shift
                $breakStart = $clockIn->copy()->addMinutes((int) floor(max(0, $workMinutes - $minutes) / 2));
                $breakEnd = $breakStart->copy()->addMinutes($minutes);

                $logToUpdateBreak->workBreak()->create([
                    'start_time' => $breakStart,
                    'end_time' => $breakEnd,
                    'note' => 'Manuell (Anfrage)',
                ]);
            }
        }

        $workTimeRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $workTimeRequest->loadMissing('user');
        if ($workTimeRequest->user?->email) {
            Mail::to($workTimeRequest->user->email)->send(new WorkTimeRequestStatusMail($workTimeRequest));
        }

        return $this->redirectToIndex()->with('success', 'Anfrage wurde genehmigt.');
    }

    public function reject(Request $request, WorkTimeRequest $workTimeRequest)
    {
        if ($workTimeRequest->status !== 'pending') {
            return $this->redirectToIndex()->with('error', 'Diese Anfrage wurde bereits bearbeitet.');
        }

        $request->validate([
            'admin_comment' => 'nullable|string|max:2000',
        ]);

        $workTimeRequest->update([
            'status' => 'rejected',
            'admin_comment' => $request->input('admin_comment'),
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $workTimeRequest->loadMissing('user');
        if ($workTimeRequest->user?->email) {
            Mail::to($workTimeRequest->user->email)->send(new WorkTimeRequestStatusMail($workTimeRequest));
        }

        return $this->redirectToIndex()->with('success', 'Anfrage wurde abgelehnt.');
    }
}
