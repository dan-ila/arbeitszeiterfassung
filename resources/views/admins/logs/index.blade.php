@extends('master.app')
@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h3 class="mb-1 fw-semibold text-secondary">
                <i class="fa fa-file-text-o me-2" aria-hidden="true"></i>
                Aktivitätsprotokoll
            </h3>
            <div class="text-secondary opacity-75">Übersicht über Aktionen und Änderungen im System.</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-secondary text-white">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <span class="fw-semibold">
                    <i class="fa fa-list-alt me-2" aria-hidden="true"></i>
                    Einträge
                </span>

                <form method="GET" action="{{ url()->current() }}" class="d-flex flex-wrap flex-xl-nowrap align-items-end gap-2 w-100" style="max-width: 1100px;">
                    <div class="flex-grow-1" style="min-width: 280px;">
                        <label class="form-label mb-1">Suche</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                <i class="fa fa-search"></i>
                            </span>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Benutzer, Aktion, Details...">
                        </div>
                    </div>

                    <div style="min-width: 200px;">
                        <label class="form-label mb-1">Aktion</label>
                        <select class="form-select" name="action">
                            <option value="">Alle</option>
                            @foreach(($actions ?? []) as $a)
                                <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="min-width: 150px;">
                        <label class="form-label mb-1">Von</label>
                        <input type="date" class="form-control" name="from" value="{{ request('from') }}">
                    </div>

                    <div style="min-width: 150px;">
                        <label class="form-label mb-1">Bis</label>
                        <input type="date" class="form-control" name="to" value="{{ request('to') }}">
                    </div>

                    <div class="d-flex gap-2 filter-actions" style="white-space: nowrap;">
                        <button class="btn btn-primary" type="submit">OK</button>
                        <a class="btn btn-outline-light" href="{{ url()->current() }}" title="Zurücksetzen">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body p-0 bg-light">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary">
                                <i class="fa fa-user me-2" aria-hidden="true"></i>
                                Benutzer
                            </th>
                            <th class="text-secondary">
                                <i class="fa fa-bolt me-2" aria-hidden="true"></i>
                                Aktion
                            </th>
                            <th class="text-secondary">
                                <i class="fa fa-info-circle me-2" aria-hidden="true"></i>
                                Details
                            </th>
                            <th class="text-secondary">
                                <i class="fa fa-clock-o me-2" aria-hidden="true"></i>
                                Zeitpunkt
                            </th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        @forelse($logs as $log)
                            @php
                                $actor = $log->user
                                    ? trim(($log->user->first_name ?? '') . ' ' . ($log->user->last_name ?? ''))
                                    : 'System';
                                $actor = $actor !== '' ? $actor : 'System';
                            @endphp
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $actor }}</td>
                                <td class="text-secondary">{{ $log->action }}</td>
                                <td class="text-secondary">{{ $log->details }}</td>
                                <td class="text-secondary">{{ $log->created_at->format('d.m.Y H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-5">
                                    <i class="fa fa-inbox me-2" aria-hidden="true"></i>
                                    Keine Logs vorhanden.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($logs, 'links'))
                <div class="p-3">
                    {{ $logs->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
