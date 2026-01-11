@extends('master.app')
@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h3 class="mb-1 fw-semibold text-secondary">
                <i class="fa fa-check-square-o me-2" aria-hidden="true"></i>
                Anfragen
            </h3>
            <div class="text-secondary opacity-75">Arbeitszeit & Urlaub prüfen, genehmigen oder ablehnen</div>
        </div>
    </div>

    @php
        $pendingWork = $pendingWorkCount ?? $requests->where('status', 'pending')->count();
        $pendingVacation = $pendingVacationCount ?? ($vacationRequests ?? collect())->where('status', 'pending')->count();
    @endphp

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-worktime" data-bs-toggle="tab" data-bs-target="#pane-worktime" type="button" role="tab" aria-controls="pane-worktime" aria-selected="true">
                <i class="fa fa-clock-o me-2" aria-hidden="true"></i>
                Arbeitszeit
                @if($pendingWork > 0)
                    <span class="badge text-bg-warning ms-2">{{ $pendingWork }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-vacation" data-bs-toggle="tab" data-bs-target="#pane-vacation" type="button" role="tab" aria-controls="pane-vacation" aria-selected="false">
                <i class="fa fa-plane me-2" aria-hidden="true"></i>
                Urlaub
                @if($pendingVacation > 0)
                    <span class="badge text-bg-warning ms-2">{{ $pendingVacation }}</span>
                @endif
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="pane-worktime" role="tabpanel" aria-labelledby="tab-worktime" tabindex="0">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-secondary text-white d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="fa fa-inbox me-2" aria-hidden="true"></i>
                        Arbeitszeit-Anfragen
                    </span>
                </div>

                <div class="card-body border-bottom bg-light">
                    <form method="GET" action="{{ url()->current() }}" class="d-flex flex-wrap flex-xl-nowrap align-items-end gap-2">
                        <div class="flex-grow-1" style="min-width: 260px;">
                            <label class="form-label mb-1">Suche</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-secondary"><i class="fa fa-search"></i></span>
                                <input type="text" class="form-control" name="work_search" value="{{ request('work_search') }}" placeholder="Name, E-Mail, Grund...">
                            </div>
                        </div>
                        <div style="min-width: 170px;">
                            <label class="form-label mb-1">Status</label>
                            <select class="form-select" name="work_status">
                                <option value="">Alle</option>
                                <option value="pending" {{ request('work_status')==='pending'?'selected':'' }}>pending</option>
                                <option value="approved" {{ request('work_status')==='approved'?'selected':'' }}>approved</option>
                                <option value="rejected" {{ request('work_status')==='rejected'?'selected':'' }}>rejected</option>
                            </select>
                        </div>
                        <div style="min-width: 150px;">
                            <label class="form-label mb-1">Typ</label>
                            <select class="form-select" name="work_type">
                                <option value="">Alle</option>
                                <option value="add" {{ request('work_type')==='add'?'selected':'' }}>add</option>
                                <option value="edit" {{ request('work_type')==='edit'?'selected':'' }}>edit</option>
                            </select>
                        </div>
                        <div style="min-width: 150px;">
                            <label class="form-label mb-1">Von</label>
                            <input type="date" class="form-control" name="work_from" value="{{ request('work_from') }}">
                        </div>
                        <div style="min-width: 150px;">
                            <label class="form-label mb-1">Bis</label>
                            <input type="date" class="form-control" name="work_to" value="{{ request('work_to') }}">
                        </div>
                        <div class="d-flex gap-2 filter-actions" style="white-space: nowrap;">
                            <button class="btn btn-primary" type="submit">Filtern</button>
                            <a class="btn btn-outline-secondary" href="{{ url()->current() }}">Zurücksetzen</a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0 bg-light">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-secondary">Status</th>
                                    <th class="text-secondary">Typ</th>
                                    <th class="text-secondary">Benutzer</th>
                                    <th class="text-secondary">Datum</th>
                                    <th class="text-secondary">Gewünscht</th>
                                    <th class="text-secondary">Pause</th>
                                    <th class="text-secondary">Grund</th>
                                    <th class="text-secondary text-end">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse($requests as $req)
                                    @php
                                        $date = \Carbon\Carbon::parse($req->requested_clock_in);
                                        $statusClass = match($req->status) {
                                            'approved' => 'text-bg-success',
                                            'rejected' => 'text-bg-danger',
                                            default => 'text-bg-warning',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge {{ $statusClass }}">{{ strtoupper($req->status) }}</span>
                                        </td>
                                        <td class="text-secondary">
                                            @if($req->type === 'add')
                                                <i class="fa fa-plus-circle me-1" aria-hidden="true"></i> Hinzufügen
                                            @else
                                                <i class="fa fa-pencil me-1" aria-hidden="true"></i> Ändern
                                            @endif
                                        </td>
                                        <td class="text-secondary">
                                            {{ $req->user?->first_name }} {{ $req->user?->last_name }}
                                        </td>
                                        <td class="text-secondary">
                                            {{ $date->format('d.m.Y') }}
                                        </td>
                                        <td class="text-secondary">
                                            {{ $date->format('H:i') }} – {{ \Carbon\Carbon::parse($req->requested_clock_out)->format('H:i') }}
                                        </td>
                                        <td class="text-secondary">
                                            <i class="fa fa-coffee me-1" aria-hidden="true"></i>
                                            {{ (int)($req->requested_break_minutes ?? 0) }} Min
                                        </td>
                                        <td class="text-secondary" style="max-width: 380px;">
                                            <span class="d-inline-block text-truncate" style="max-width: 380px;" title="{{ $req->reason }}">
                                                {{ $req->reason ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if($req->status === 'pending')
                                                <form method="POST" action="{{ route('admin.worktime.requests.approve', $req) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fa fa-check me-1" aria-hidden="true"></i>
                                                        Genehmigen
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.worktime.requests.reject', $req) }}" data-confirm="Anfrage wirklich ablehnen?" data-confirm-title="Anfrage ablehnen" data-confirm-button="Ablehnen" data-confirm-variant="danger" class="d-inline ms-1">
                                                    @csrf
                                                    <input type="hidden" name="admin_comment" value="">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fa fa-times me-1" aria-hidden="true"></i>
                                                        Ablehnen
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-secondary opacity-75">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-secondary opacity-75 py-4">
                                            <i class="fa fa-smile-o me-2" aria-hidden="true"></i>
                                            Keine Arbeitszeit-Anfragen vorhanden
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pane-vacation" role="tabpanel" aria-labelledby="tab-vacation" tabindex="0">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-secondary text-white d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="fa fa-plane me-2" aria-hidden="true"></i>
                        Urlaubsanträge
                    </span>
                </div>

                <div class="card-body border-bottom bg-light">
                    <form method="GET" action="{{ url()->current() }}" class="d-flex flex-wrap flex-xl-nowrap align-items-end gap-2">
                        <div class="flex-grow-1" style="min-width: 260px;">
                            <label class="form-label mb-1">Suche</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-secondary"><i class="fa fa-search"></i></span>
                                <input type="text" class="form-control" name="vac_search" value="{{ request('vac_search') }}" placeholder="Name oder E-Mail...">
                            </div>
                        </div>
                        <div style="min-width: 170px;">
                            <label class="form-label mb-1">Status</label>
                            <select class="form-select" name="vac_status">
                                <option value="">Alle</option>
                                <option value="pending" {{ request('vac_status')==='pending'?'selected':'' }}>pending</option>
                                <option value="approved" {{ request('vac_status')==='approved'?'selected':'' }}>approved</option>
                                <option value="rejected" {{ request('vac_status')==='rejected'?'selected':'' }}>rejected</option>
                            </select>
                        </div>
                        <div style="min-width: 170px;">
                            <label class="form-label mb-1">Start ab</label>
                            <input type="date" class="form-control" name="vac_from" value="{{ request('vac_from') }}">
                        </div>
                        <div style="min-width: 170px;">
                            <label class="form-label mb-1">Ende bis</label>
                            <input type="date" class="form-control" name="vac_to" value="{{ request('vac_to') }}">
                        </div>
                        <div class="d-flex gap-2 filter-actions" style="white-space: nowrap;">
                            <button class="btn btn-primary" type="submit">Filtern</button>
                            <a class="btn btn-outline-secondary" href="{{ url()->current() }}">Zurücksetzen</a>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0 bg-light">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-secondary">Status</th>
                                    <th class="text-secondary">Benutzer</th>
                                    <th class="text-secondary">Zeitraum</th>
                                    <th class="text-secondary">Tage</th>
                                    <th class="text-secondary text-end">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse(($vacationRequests ?? []) as $vac)
                                    @php
                                        $statusClass = match($vac->status) {
                                            'approved' => 'text-bg-success',
                                            'rejected' => 'text-bg-danger',
                                            default => 'text-bg-warning',
                                        };
                                        $start = \Carbon\Carbon::parse($vac->start_date);
                                        $end = \Carbon\Carbon::parse($vac->end_date);
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge {{ $statusClass }}">{{ strtoupper($vac->status) }}</span>
                                        </td>
                                        <td class="text-secondary">
                                            {{ $vac->user?->first_name }} {{ $vac->user?->last_name }}
                                        </td>
                                        <td class="text-secondary">
                                            {{ $start->format('d.m.Y') }} – {{ $end->format('d.m.Y') }}
                                        </td>
                                        <td class="text-secondary">
                                            {{ abs((int)($vac->days ?? 0)) }}
                                        </td>
                                        <td class="text-end">
                                            @if($vac->status === 'pending')
                                                <form method="POST" action="{{ route('admin.vacation.requests.approve', $vac) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="fa fa-check me-1" aria-hidden="true"></i>
                                                        Genehmigen
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.vacation.requests.reject', $vac) }}" class="d-inline ms-1">
                                                    @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Urlaubsantrag wirklich ablehnen?" data-confirm-title="Urlaubsantrag ablehnen" data-confirm-button="Ablehnen" data-confirm-variant="danger">
                                                        <i class="fa fa-times me-1" aria-hidden="true"></i>
                                                        Ablehnen
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-secondary opacity-75">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary opacity-75 py-4">
                                            <i class="fa fa-smile-o me-2" aria-hidden="true"></i>
                                            Keine Urlaubsanträge vorhanden
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
