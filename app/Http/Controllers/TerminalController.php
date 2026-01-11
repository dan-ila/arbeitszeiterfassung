<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TerminalController extends Controller
{
    // List all terminals
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $terminalsQuery = Device::query()->orderByDesc('id');

        if ($search !== '') {
            $terminalsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('api_token', 'like', "%{$search}%");
            });
        }

        $terminals = $terminalsQuery->paginate(25)->withQueryString();

        return view('admins.terminals.index', compact('terminals', 'search'));
    }

    // Show create form
    public function create()
    {
        return view('admins.terminals.create');
    }

    // Store new terminal
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'enabled' => 'nullable|boolean',
        ]);

        Device::create([
            'name' => $request->name,
            'api_token' => Str::random(60), // generate random API token
            'enabled' => (bool) $request->boolean('enabled', true),
        ]);

        return redirect()->route('terminals.index')
            ->with('success', 'Terminal wurde erstellt.');
    }

    // Show edit form
    public function edit(Device $terminal)
    {
        return view('admins.terminals.edit', compact('terminal'));
    }

    // Update terminal
    public function update(Request $request, Device $terminal)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'enabled' => 'nullable|boolean',
        ]);

        $terminal->update([
            'name' => $request->name,
            'enabled' => (bool) $request->boolean('enabled', true),
        ]);

        return redirect()->route('terminals.index')
            ->with('success', 'Terminal wurde aktualisiert.');
    }

    // Delete terminal
    public function destroy(Device $terminal)
    {
        $terminal->delete();

        return redirect()->route('terminals.index')
            ->with('success', 'Terminal wurde gelöscht.');
    }

    public function enable(Device $terminal)
    {
        $terminal->update(['enabled' => true]);

        return redirect()->route('terminals.index')
            ->with('success', 'Terminal wurde aktiviert.');
    }

    public function disable(Device $terminal)
    {
        $terminal->update(['enabled' => false]);

        return redirect()->route('terminals.index')
            ->with('success', 'Terminal wurde deaktiviert.');
    }
}
