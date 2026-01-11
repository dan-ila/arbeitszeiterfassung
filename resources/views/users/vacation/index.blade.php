@extends('master.app')
@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h3 class="mb-1 fw-semibold text-secondary">
                <i class="fa fa-plane me-2" aria-hidden="true"></i>
                Urlaub
            </h3>
            <div class="text-secondary opacity-75">Verwalte deine Urlaubsanträge und den Status.</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('users.vacation.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-2" aria-hidden="true"></i>
                Neuen Urlaub beantragen
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body bg-light">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary opacity-75">Verbleibende Tage</div>
                            <div class="display-6 fw-semibold text-secondary mb-0">{{ $remainingDays }}</div>
                        </div>
                        <div class="text-primary" style="font-size: 2rem; line-height: 1;">
                            <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-secondary text-white">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <span class="fw-semibold">
                    <i class="fa fa-list-alt me-2" aria-hidden="true"></i>
                    Urlaubsanträge
                </span>

                <form method="GET" action="{{ url()->current() }}" class="d-flex flex-wrap flex-lg-nowrap align-items-end gap-2">
                    @php
                        $y = (int)($year ?? now()->year);
                        $years = [$y + 1, $y, $y - 1, $y - 2];
                        $years = array_values(array_unique(array_filter($years)));
                    @endphp

                    <div style="min-width: 140px;">
                        <select class="form-select" name="year">
                            @foreach($years as $yy)
                                <option value="{{ $yy }}" {{ (int)request('year', $y) === (int)$yy ? 'selected' : '' }}>{{ $yy }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="min-width: 180px;">
                        <select class="form-select" name="status">
                            <option value="">Alle</option>
                            <option value="pending" {{ request('status')==='pending'?'selected':'' }}>pending</option>
                            <option value="approved" {{ request('status')==='approved'?'selected':'' }}>approved</option>
                            <option value="rejected" {{ request('status')==='rejected'?'selected':'' }}>rejected</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2 filter-actions" style="white-space: nowrap;">
                        <button class="btn btn-primary" type="submit">Filtern</button>
                        <a class="btn btn-outline-light" href="{{ url()->current() }}">
                            <i class="fa fa-times me-1" aria-hidden="true"></i>
                            Zurücksetzen
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
                                <i class="fa fa-play me-2" aria-hidden="true"></i>
                                Start
                            </th>
                            <th class="text-secondary">
                                <i class="fa fa-stop me-2" aria-hidden="true"></i>
                                Ende
                            </th>
                            <th class="text-secondary">
                                <i class="fa fa-info-circle me-2" aria-hidden="true"></i>
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        @forelse($vacations as $vacation)
                            @php
                                $status = strtolower((string) $vacation->status);
                                $badge = match ($status) {
                                    'approved', 'genehmigt' => 'text-bg-success',
                                    'rejected', 'abgelehnt' => 'text-bg-danger',
                                    'pending', 'offen' => 'text-bg-warning',
                                    default => 'text-bg-secondary',
                                };
                            @endphp
                            <tr>
                                <td class="text-secondary">
                                    {{ \Carbon\Carbon::parse($vacation->start_date)->format('d.m.Y') }}
                                </td>
                                <td class="text-secondary">
                                    {{ \Carbon\Carbon::parse($vacation->end_date)->format('d.m.Y') }}
                                </td>
                                <td>
                                    <span class="badge {{ $badge }}">
                                        {{ ucfirst($vacation->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-secondary py-5">
                                    <i class="fa fa-inbox me-2" aria-hidden="true"></i>
                                    Keine Urlaube beantragt
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
