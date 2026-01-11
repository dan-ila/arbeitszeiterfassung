<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $action = $request->query('action');
        $from = $request->query('from');
        $to = $request->query('to');

        $logsQuery = \App\Models\Log::query()->with('user')->latest();

        if (is_string($action) && $action !== '') {
            $logsQuery->where('action', $action);
        } else {
            $action = null;
        }

        if ($search !== '') {
            $logsQuery->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (is_string($from) && $from !== '') {
            $logsQuery->whereDate('created_at', '>=', $from);
        } else {
            $from = null;
        }

        if (is_string($to) && $to !== '') {
            $logsQuery->whereDate('created_at', '<=', $to);
        } else {
            $to = null;
        }

        $actions = \App\Models\Log::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->filter()
            ->values();

        $logs = $logsQuery->paginate(50)->withQueryString();

        return view('admins.logs.index', compact('logs', 'actions', 'search', 'action', 'from', 'to'));
    }
}