<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\WorkLog;
use App\Traits\LogActivity;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class RFIDController extends Controller
{
    use LogActivity;
    
    public function scan(Request $request){
        $request->validate([
            'rfid_uid' => 'required|string',
        ]);

        $user = User::where('rfid_uid', $request->rfid_uid)->first();

        if (!$user) {
            return response()->json(['message' => 'RFID not recognized.'], 404);
        }

        $lastLog = WorkLog::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$lastLog || $lastLog->clock_out) {
            return $this->handleClockAction($user, 'clock_in');
        }

        $ongoingBreak = $lastLog->workBreak()->whereNull('end_time')->first();
        if ($ongoingBreak) {
            return $this->handleBreakEnd($user);
        }

        $breaksCount = $lastLog->workBreak()->count();
        if ($breaksCount === 0) {
            return $this->handleBreakStart($user);
        } else {
            return $this->handleClockAction($user, 'clock_out');
        }
    }

 protected function handleClockAction(User $user, $type)
    {
        if ($type === 'clock_in') {
            $log = WorkLog::create([
                'user_id' => $user->id,
                'clock_in' => now(),
                'source' => 'terminal',
            ]);

            $this->logAction($user->id, 'clock_in', 'User clocked in via RFID.');

            return response()->json([
                'message' => 'Clocked in successfully.',
                'time' => now()->format('H:i:s'),
            ]);
        } else {
            $currentLog = WorkLog::where('user_id', $user->id)
                ->whereNull('clock_out')
                ->latest()
                ->first();

            $currentLog->clock_out = now();
            $currentLog->save();

            $this->logAction($user->id, 'clock_out', 'User clocked out via RFID.');

            return response()->json([
                'message' => 'Clocked out successfully.',
                'time' => now()->format('H:i:s'),
            ]);
        }
    }

    protected function handleBreakStart(User $user) {
        $currentLog = WorkLog::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->latest()
            ->first();

        $currentLog->workBreak()->create([
            'start_time' => now(),
        ]);

        $this->logAction($user->id, 'break_start', 'User started break.');

        return response()->json([
            'message' => 'Break started successfully.',
            'time' => now()->format('H:i:s'),
        ]);
    }

    protected function handleBreakEnd(User $user){
        $currentLog = WorkLog::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->latest()
            ->first();

        $ongoingBreak = $currentLog->workBreak()->whereNull('end_time')->first();
        $ongoingBreak->end_time = now();
        $ongoingBreak->save();

        $this->logAction($user->id, 'break_end', 'User ended break.');

        return response()->json([
            'message' => 'Break ended successfully.',
            'time' => now()->format('H:i:s'),
        ]);
    }
}
