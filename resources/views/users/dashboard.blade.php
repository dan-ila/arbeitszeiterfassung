@extends('master.app')
@section('content')
<div class="container py-4">
    @php
        $year = request()->get('year', now()->year);
        $month = request()->get('month', now()->month);
        $prev = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth();
        $next = \Carbon\Carbon::createFromDate($year, $month, 1)->addMonth();
        $monthName = \Carbon\Carbon::createFromDate($year, $month, 1)->locale('de')->isoFormat('MMMM YYYY');
    @endphp

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h3 class="mb-1 fw-semibold text-secondary">
                <i class="fa fa-dashboard me-2" aria-hidden="true"></i>
                Dashboard
            </h3>
            <div class="text-secondary opacity-75">
                {{ ucfirst($monthName) }}
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a
                href="{{ route('users.dashboard', ['month' => $prev->month, 'year' => $prev->year]) }}"
                class="btn btn-outline-primary"
            >
                <i class="fa fa-chevron-left me-2" aria-hidden="true"></i>
                Vorheriger Monat
            </a>
            <a
                href="{{ route('users.dashboard', ['month' => $next->month, 'year' => $next->year]) }}"
                class="btn btn-primary"
            >
                Nächster Monat
                <i class="fa fa-chevron-right ms-2" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-secondary text-white rounded-top-4 d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="fa fa-users me-2" aria-hidden="true"></i>
                        Benutzer
                    </span>
                </div>
                <div class="card-body bg-light text-center py-4">
                    <div class="display-6 fw-semibold text-secondary mb-1">{{ $usersCount }}</div>
                    <div class="text-secondary opacity-75">Gesamt</div>
                </div>
                <div class="card-footer bg-primary text-white rounded-bottom-4 d-flex align-items-center justify-content-center gap-2">
                    <i class="fa fa-user-circle" aria-hidden="true"></i>
                    <span>Aktive Benutzer: {{ $activeUsers }}</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-secondary text-white rounded-top-4 d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="fa fa-calendar me-2" aria-hidden="true"></i>
                        Heute
                    </span>
                </div>
                <div class="card-body bg-light text-center py-4">
                    <div class="h4 fw-semibold text-secondary mb-1">{{ $currentDayOfWeek }}</div>
                    <div class="text-secondary opacity-75">Aktueller Wochentag</div>
                </div>
                <div class="card-footer bg-primary text-white rounded-bottom-4 d-flex align-items-center justify-content-center gap-2">
                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                    <span id="txt" class="fw-semibold"></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-secondary text-white rounded-top-4 d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">
                        <i class="fa fa-hourglass-half me-2" aria-hidden="true"></i>
                        Gearbeitete Stunden
                    </span>
                </div>
                <div class="card-body bg-light text-center py-4">
                    <div class="display-6 fw-semibold text-secondary mb-1">{{ $workedHours }}/160</div>
                    <div class="text-secondary opacity-75">Monat</div>
                </div>
                <div class="card-footer bg-primary text-white rounded-bottom-4 d-flex align-items-center justify-content-center gap-2">
                    <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                    <span>{{ \Carbon\Carbon::create($year, $month)->locale('de')->isoFormat('MMMM YYYY') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Worklog Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-secondary text-white d-flex align-items-center justify-content-between">
            <span class="fw-semibold">
                <i class="fa fa-list-alt me-2" aria-hidden="true"></i>
                Arbeitszeiten
            </span>
        </div>

        <div class="card-body p-0 bg-light">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary">
                                <i class="fa fa-calendar-o me-2" aria-hidden="true"></i>
                                Wochentag
                            </th>
                            <th class="text-secondary">Tag</th>
                            <th class="text-secondary">
                                <i class="fa fa-sign-in me-2" aria-hidden="true"></i>
                                Startzeit
                            </th>
                            <th class="text-secondary">
                                <i class="fa fa-sign-out me-2" aria-hidden="true"></i>
                                Endzeit
                            </th>
                            <th class="text-secondary">
                                <i class="fa fa-coffee me-2" aria-hidden="true"></i>
                                Pause
                            </th>
                            <th class="text-secondary">
                                <i class="fa fa-info-circle me-2" aria-hidden="true"></i>
                                Quelle
                            </th>
                            <th class="text-secondary text-end">
                                <i class="fa fa-wrench me-2" aria-hidden="true"></i>
                                Aktion
                            </th>
                        </tr>
                    </thead>

                    <tbody class="table-group-divider">
                        @php
                            $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
                        @endphp

                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $dateObj = \Carbon\Carbon::create($year, $month, $day);
                                $date = $dateObj->format('Y-m-d');
                                $weekday = ucfirst($dateObj->locale('de')->isoFormat('dddd'));
                                $holidayName = $holidaysInMonth[$date] ?? null;
                                $isWeekend = in_array($dateObj->dayOfWeekIso, [6, 7], true);
                                $logsForDay = $worklogs
                                    ->filter(fn($log) => \Carbon\Carbon::parse($log->clock_in)->format('Y-m-d') === $date)
                                    ->sortBy('clock_in')
                                    ->values();
                            @endphp

                            @if($logsForDay->count())
                                @php
                                    $firstLog = $logsForDay->first();
                                    $lastLogWithOut = $logsForDay
                                        ->filter(fn($l) => !is_null($l->clock_out))
                                        ->sortByDesc('clock_out')
                                        ->first();
                                    $hasOpenShift = (bool) $logsForDay->first(fn($l) => is_null($l->clock_out));

                                    $totalBreakMinutes = (int) $logsForDay->sum(function ($l) use ($breakDurations) {
                                        return (int) ($breakDurations[$l->id] ?? 0);
                                    });

                                    $sources = $logsForDay
                                        ->map(fn($l) => $l->source ?? 'terminal')
                                        ->unique()
                                        ->values();
                                @endphp

                                <tr class="{{ ($holidayName || $isWeekend) ? 'table-secondary' : '' }}">
                                    <td class="text-secondary fw-semibold">{{ $weekday }}</td>
                                    <td class="text-secondary">
                                        {{ $dateObj->format('d.m.Y') }}
                                        @if($holidayName)
                                            <span class="badge text-bg-light border text-secondary ms-2" title="{{ $holidayName }}">
                                                <i class="fa fa-flag-o me-1" aria-hidden="true"></i>
                                                Feiertag
                                            </span>
                                        @elseif($isWeekend)
                                            <span class="badge text-bg-light border text-secondary ms-2" title="Wochenende">
                                                <i class="fa fa-calendar me-1" aria-hidden="true"></i>
                                                Wochenende
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-secondary">{{ \Carbon\Carbon::parse($firstLog->clock_in)->format('H:i') }}</td>
                                    <td>
                                        @if($hasOpenShift)
                                            <span class="badge text-bg-warning">Noch nicht ausgestempelt</span>
                                        @elseif($lastLogWithOut)
                                            <span class="text-secondary">{{ \Carbon\Carbon::parse($lastLogWithOut->clock_out)->format('H:i') }}</span>
                                        @else
                                            <span class="text-secondary opacity-75">—</span>
                                        @endif
                                    </td>
                                    <td class="text-secondary">{{ $totalBreakMinutes }} Min</td>
                                    <td class="text-secondary">
                                        @if($sources->count() === 1)
                                            @if($sources->first() === 'web')
                                                <span title="Web"><i class="fa fa-globe" aria-hidden="true"></i></span>
                                            @else
                                                <span title="Terminal"><i class="fa fa-desktop" aria-hidden="true"></i></span>
                                            @endif
                                        @else
                                            <span title="Gemischt (Web + Terminal)">
                                                <i class="fa fa-globe me-1" aria-hidden="true"></i>
                                                <i class="fa fa-desktop" aria-hidden="true"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            @if(!$holidayName && !$isWeekend)
                                                @if(!$hasOpenShift && !($hasShiftAfterBreak[$date] ?? false))
                                                    <a href="{{ route('users.worklogs.create', ['date' => $date]) }}" class="btn btn-sm btn-primary">
                                                        <i class="fa fa-plus me-1" aria-hidden="true"></i>
                                                        Schicht hinzufügen
                                                    </a>
                                                @elseif($hasOpenShift)
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Offene Schicht – erst ausstempeln">
                                                        <i class="fa fa-plus me-1" aria-hidden="true"></i>
                                                        Schicht hinzufügen
                                                    </button>
                                                @endif
                                            @endif

                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa fa-pencil me-1" aria-hidden="true"></i>
                                                    Änderung beantragen
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @foreach($logsForDay as $log)
                                                        @php
                                                            $labelStart = \Carbon\Carbon::parse($log->clock_in)->format('H:i');
                                                            $labelEnd = $log->clock_out ? \Carbon\Carbon::parse($log->clock_out)->format('H:i') : 'offen';
                                                            $src = $log->source ?? 'terminal';
                                                        @endphp
                                                        <li>
                                                            <div class="d-flex align-items-center justify-content-between gap-2 px-2">
                                                                <a class="dropdown-item flex-grow-1 px-2" href="{{ route('worktime.requests.edit', $log) }}">
                                                                    @if($src === 'web')
                                                                        <i class="fa fa-globe me-2" aria-hidden="true"></i>
                                                                    @else
                                                                        <i class="fa fa-desktop me-2" aria-hidden="true"></i>
                                                                    @endif
                                                                    {{ $labelStart }}–{{ $labelEnd }}
                                                                </a>

                                                                @if($src === 'web')
                                                                    <form method="POST" action="{{ route('users.worklogs.destroy', $log) }}" data-confirm="Diese Schicht wirklich löschen?" data-confirm-title="Schicht löschen" data-confirm-button="Löschen" data-confirm-variant="danger" class="m-0">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Schicht löschen">
                                                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                <tr class="{{ ($holidayName || $isWeekend) ? 'table-secondary' : '' }}">
                                    <td class="text-secondary fw-semibold opacity-75">{{ $weekday }}</td>
                                    <td class="text-secondary opacity-75">{{ $dateObj->format('d.m.Y') }}</td>
                                    <td colspan="3" class="text-center text-secondary opacity-75">
                                        @if($holidayName)
                                            <i class="fa fa-flag-o me-2" aria-hidden="true"></i>
                                            Feiertag: {{ $holidayName }}
                                        @elseif($isWeekend)
                                            <i class="fa fa-calendar me-2" aria-hidden="true"></i>
                                            Wochenende
                                        @else
                                            <i class="fa fa-minus-circle me-2" aria-hidden="true"></i>
                                            Keine Arbeitszeit gestochen
                                        @endif
                                    </td>
                                    <td class="text-secondary opacity-75 text-center">—</td>
                                    <td class="text-end">
                                        @if(!$holidayName && !$isWeekend)
                                            <a href="{{ route('users.worklogs.create', ['date' => $date]) }}" class="btn btn-sm btn-primary">
                                                <i class="fa fa-plus me-1" aria-hidden="true"></i>
                                                Zeit hinzufügen
                                            </a>
                                        @else
                                            <span class="text-secondary opacity-75">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function startTime() {
        const today = new Date();
        let h = today.getHours();
        let m = today.getMinutes();
        let s = today.getSeconds();
        m = checkTime(m);
        s = checkTime(s);
        document.getElementById('txt').innerHTML = h + ":" + m + ":" + s;
        setTimeout(startTime, 500);
    }
    function checkTime(i) {
        return i < 10 ? "0" + i : i;
    }
    startTime();
</script>
@endsection
