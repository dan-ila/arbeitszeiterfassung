<?php

namespace App\Http\Controllers;

use App\Models\WorkBreak;
use App\Models\WorkLog;
use App\Support\Holidays\HessenHolidays;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebWorkLogController extends Controller
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

        return view('users.worklogs.form', [
            'date' => $date,
            'existingLogCount' => $existingLogCount,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'blocks' => 'required|array|min:1|max:20',
            'blocks.*.type' => 'required|in:shift,break',
            'blocks.*.start_time' => 'required|date_format:H:i',
            'blocks.*.end_time' => 'required|date_format:H:i',
        ]);

        $date = Carbon::createFromFormat('Y-m-d', $request->string('date'));

        if (HessenHolidays::isBlockedForAdd($date)) {
            $holidayName = HessenHolidays::holidayName($date);
            $msg = $holidayName
                ? "An diesem Tag (Feiertag: {$holidayName}) kann keine Arbeitszeit hinzugefügt werden."
                : 'An Wochenenden kann keine Arbeitszeit hinzugefügt werden.';

            return back()->withErrors(['date' => $msg])->withInput();
        }

        $rawBlocks = $request->input('blocks', []);
        $parsedShifts = [];
        $parsedBreaks = [];

        foreach ($rawBlocks as $index => $block) {
            $type = $block['type'] ?? null;
            $start = Carbon::createFromFormat('Y-m-d H:i', $date->toDateString().' '.$block['start_time']);
            $end = Carbon::createFromFormat('Y-m-d H:i', $date->toDateString().' '.$block['end_time']);

            if (!$end->gt($start)) {
                return back()->withErrors([
                    "blocks.$index.end_time" => 'Ende muss nach dem Beginn liegen.',
                ])->withInput();
            }

            if ($type === 'break') {
                $parsedBreaks[] = [
                    'index' => $index,
                    'start' => $start,
                    'end' => $end,
                    'minutes' => $end->diffInMinutes($start),
                ];
            } else {
                $parsedShifts[] = [
                    'index' => $index,
                    'clock_in' => $start,
                    'clock_out' => $end,
                    'work_minutes' => $end->diffInMinutes($start),
                ];
            }
        }

        if (count($parsedShifts) === 0) {
            return back()->withErrors([
                'blocks' => 'Mindestens eine Schicht ist erforderlich.',
            ])->withInput();
        }

        $logsThatDay = WorkLog::where('user_id', Auth::id())
            ->whereDate('clock_in', $date->toDateString())
            ->get();

        $hasOpenShift = $logsThatDay->contains(fn ($l) => $l->clock_out === null);
        if ($hasOpenShift) {
            return back()->withErrors([
                'date' => 'Für diesen Tag gibt es eine offene Schicht (ohne Endzeit).',
            ])->withInput();
        }

        foreach ($logsThatDay as $existing) {
            if (!$existing->clock_out) {
                continue;
            }
            $existingStart = Carbon::parse($existing->clock_in);
            $existingEnd = Carbon::parse($existing->clock_out);

            foreach ($parsedShifts as $s) {
                $overlaps = $s['clock_in']->lt($existingEnd) && $s['clock_out']->gt($existingStart);
                if ($overlaps) {
                    return back()->withErrors([
                        "blocks.{$s['index']}.start_time" => 'Die gewünschte Schicht überschneidet sich mit einer bestehenden Arbeitszeit.',
                    ])->withInput();
                }
            }
        }

        // Prevent overlaps within submitted shifts (split shifts must be non-overlapping)
        usort($parsedShifts, fn ($a, $b) => $a['clock_in']->getTimestamp() <=> $b['clock_in']->getTimestamp());
        for ($i = 1; $i < count($parsedShifts); $i++) {
            $prev = $parsedShifts[$i - 1];
            $curr = $parsedShifts[$i];
            if ($curr['clock_in']->lt($prev['clock_out'])) {
                return back()->withErrors([
                    "blocks.{$curr['index']}.start_time" => 'Diese Schicht überschneidet sich mit einer anderen Schicht im Formular.',
                ])->withInput();
            }
        }

        // Validate breaks: no overlap with shifts, no overlap among breaks, and must be within shift span
        if (count($parsedBreaks) > 0) {
            $firstShiftStart = $parsedShifts[0]['clock_in']->copy();
            $lastShiftEnd = $parsedShifts[count($parsedShifts) - 1]['clock_out']->copy();

            usort($parsedBreaks, fn ($a, $b) => $a['start']->getTimestamp() <=> $b['start']->getTimestamp());

            for ($i = 0; $i < count($parsedBreaks); $i++) {
                $br = $parsedBreaks[$i];

                if ($br['start']->lt($firstShiftStart) || $br['end']->gt($lastShiftEnd)) {
                    return back()->withErrors([
                        "blocks.{$br['index']}.start_time" => 'Pausen müssen innerhalb der Arbeitszeit (zwischen erster und letzter Schicht) liegen.',
                    ])->withInput();
                }

                // no overlap with shifts
                foreach ($parsedShifts as $s) {
                    $overlaps = $br['start']->lt($s['clock_out']) && $br['end']->gt($s['clock_in']);
                    if ($overlaps) {
                        return back()->withErrors([
                            "blocks.{$br['index']}.start_time" => 'Pausen dürfen sich nicht mit einer Schicht überschneiden.',
                        ])->withInput();
                    }
                }

                // no overlap with other breaks
                if ($i > 0) {
                    $prev = $parsedBreaks[$i - 1];
                    if ($br['start']->lt($prev['end'])) {
                        return back()->withErrors([
                            "blocks.{$br['index']}.start_time" => 'Pausen dürfen sich nicht überschneiden.',
                        ])->withInput();
                    }
                }
            }
        }

        // Day-level break rule: if total work time > 6h then total breaks >= 30m
        $totalWorkMinutes = array_sum(array_map(fn ($s) => $s['work_minutes'], $parsedShifts));
        $totalBreakMinutes = array_sum(array_map(fn ($b) => $b['minutes'], $parsedBreaks));
        if ($totalWorkMinutes > 6 * 60 && $totalBreakMinutes < 30) {
            return back()->withErrors([
                'blocks' => 'Bei mehr als 6 Stunden Gesamtarbeitszeit müssen die Pausen insgesamt mindestens 30 Minuten betragen.',
            ])->withInput();
        }

        $createdLogs = [];
        foreach ($parsedShifts as $s) {
            $log = WorkLog::create([
                'user_id' => Auth::id(),
                'clock_in' => $s['clock_in'],
                'clock_out' => $s['clock_out'],
                'source' => 'web',
            ]);

            $createdLogs[] = [
                'model' => $log,
                'clock_in' => Carbon::parse($log->clock_in),
                'clock_out' => Carbon::parse($log->clock_out),
            ];
        }

        // Assign each break to the preceding shift (keeps current DB schema: breaks belong to a work_log)
        foreach ($parsedBreaks as $br) {
            $target = null;
            foreach ($createdLogs as $l) {
                if ($l['clock_out']->lte($br['start'])) {
                    $target = $l;
                } else {
                    break;
                }
            }

            if (!$target) {
                // Should not happen due to validation, but keep it safe
                continue;
            }

            WorkBreak::create([
                'work_log_id' => $target['model']->id,
                'start_time' => $br['start'],
                'end_time' => $br['end'],
                'note' => 'Manuell (Web)',
            ]);
        }

        return redirect()->route('users.dashboard', [
            'year' => $date->year,
            'month' => $date->month,
        ])->with('success', 'Schichten wurden hinzugefügt.');
    }

    public function destroy(WorkLog $workLog)
    {
        if ($workLog->user_id !== Auth::id()) {
            abort(403);
        }

        $date = Carbon::parse($workLog->clock_in);

        $source = $workLog->source ?? 'terminal';
        if ($source !== 'web') {
            return redirect()->route('users.dashboard', [
                'year' => $date->year,
                'month' => $date->month,
            ])->with('error', 'Nur manuell (Web) erfasste Arbeitszeiten können direkt gelöscht werden.');
        }

        $workLog->workBreak()->delete();
        $workLog->delete();

        return redirect()->route('users.dashboard', [
            'year' => $date->year,
            'month' => $date->month,
        ])->with('success', 'Schicht wurde gelöscht.');
    }
}
