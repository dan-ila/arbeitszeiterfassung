<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\WorkBreak;
use App\Models\WorkLog;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Queue\Worker;

class RFIDController extends Controller
{
    public function __construct(){
        $this->middleware('auth:sanctum')->except('login');
    }

    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $request->password !== $user->password) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('arduino')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

public function scan(Request $request) {
    $request->validate([
        'rfid_uid' => 'required|string',
    ]); 

    $user = User::where('rfid_uid', $request->rfid_uid)->first();
    
    if(!$user) {
        return response()->json(['message' => 'RFID not recognized.'], 404);
    }

    $lastLog = WorkLog::where('user_id', $user->id)
        ->latest()
        ->first();

    if(!$lastLog || $lastLog->clock_out) {
        return $this->handleClockAction($user, 'clock_in');
    }

    $ongoingBreak = $lastLog->workBreak()->whereNull('end_time')->first();

    if($ongoingBreak) {
        return $this->handleBreakEnd($user);
    }

    $breaksCount = $lastLog->workBreak()->count();

    if($breaksCount === 0) {
        return $this->handleBreakStart($user);
    } else {
        return $this->handleClockAction($user, 'clock_out');
    }
}

    protected function handleClockAction(User $user ,$type) {
        if($type === 'clock_in'){
            $log = WorkLog::create([
                'user_id' => $user->id,
                'clock_in' => now(),
            ]);

            return response()->json([
                'message' => 'Clocked in successfully.',
                'work_log' => $log
            ]);
        } else {
            $currentLog = WorkLog::where('user_id', $user->id)
                ->whereNull('clock_out')
                ->latest()
                ->first();

            if(!$currentLog) {
                return response()->json(['message' => 'No active work log found. Please clock in first.'], 400);
            }

            $currentLog->clock_out = now();
            $currentLog->save();

            return response()->json([
                'message' => 'Clocked out successfully.',
                'work_log' => $currentLog
            ]);
        }
    }

    protected function handleBreakStart(User $user) {
        $currentLog = WorkLog::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->latest()
            ->first();

        if(!$currentLog) {
            return response()->json(['message' => 'No active work log found. Please clock in first.'], 400);
        }

        $break = $currentLog->WorkBreak()->create([
            'start_time' => now(),
        ]);

        return response()->json(['message' => 'Break started successfully.'], 200);
    }

    protected function handleBreakEnd(User $user) {
        $currentLog = WorkLog::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->latest()
            ->first();

        $ongoingBreak = $currentLog->workBreak()
            ->whereNull('end_time')
            ->first();

        if(!$ongoingBreak) {
            return response()->json(['message' => 'No ongoing break found.'], 400);
        }

        $ongoingBreak->end_time = now();
        $ongoingBreak->save();

        return response()->json(['message' => 'Break ended successfully.'], 200);
    }
}
