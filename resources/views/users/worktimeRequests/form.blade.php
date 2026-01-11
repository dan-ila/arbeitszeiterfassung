@extends('master.app')
@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h3 class="mb-1 fw-semibold text-secondary">
                <i class="fa fa-pencil-square-o me-2" aria-hidden="true"></i>
                @if($mode === 'add') Arbeitszeit hinzufügen @else Änderung beantragen @endif
            </h3>
            <div class="text-secondary opacity-75">
                {{ \Carbon\Carbon::parse($date)->locale('de')->isoFormat('DD.MM.YYYY (dddd)') }}
            </div>
        </div>

        <a href="{{ route('users.dashboard', ['year' => \Carbon\Carbon::parse($date)->year, 'month' => \Carbon\Carbon::parse($date)->month]) }}" class="btn btn-outline-primary">
            <i class="fa fa-arrow-left me-2" aria-hidden="true"></i>
            Zurück
        </a>
    </div>

    @if($mode === 'add' && ($existingLogCount ?? 0) > 0)
        <div class="alert alert-info">
            <i class="fa fa-info-circle me-2" aria-hidden="true"></i>
            Für diesen Tag gibt es bereits {{ $existingLogCount }} Schicht(en). Du kannst eine weitere Schicht hinzufügen (ohne Überschneidung).
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-secondary text-white">
            <span class="fw-semibold">
                <i class="fa fa-clock-o me-2" aria-hidden="true"></i>
                Angaben
            </span>
        </div>

        <div class="card-body bg-light">
            <form method="POST" action="{{ route('worktime.requests.store') }}">
                @csrf
                <input type="hidden" name="mode" value="{{ $mode }}">
                <input type="hidden" name="date" value="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}">
                <input type="hidden" name="work_log_id" value="{{ $workLog?->id }}">

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label text-secondary">Startzeit</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-sign-in" aria-hidden="true"></i></span>
                            <input
                                type="time"
                                name="start_time"
                                class="form-control"
                                value="{{ old('start_time', $workLog ? \Carbon\Carbon::parse($workLog->clock_in)->format('H:i') : '') }}"
                                required
                            >
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-secondary">Endzeit</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-sign-out" aria-hidden="true"></i></span>
                            <input
                                type="time"
                                name="end_time"
                                class="form-control"
                                value="{{ old('end_time', $workLog && $workLog->clock_out ? \Carbon\Carbon::parse($workLog->clock_out)->format('H:i') : '') }}"
                                required
                            >
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-secondary">Begründung (optional)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-commenting-o" aria-hidden="true"></i></span>
                            <textarea name="reason" rows="3" class="form-control" placeholder="z.B. vergessen auszustempeln…">{{ old('reason') }}</textarea>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label text-secondary">Pause (Min.)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-coffee" aria-hidden="true"></i></span>
                            <input
                                type="number"
                                name="break_minutes"
                                min="0"
                                step="5"
                                class="form-control"
                                value="{{ old('break_minutes', $breakMinutes ?? 0) }}"
                                required
                            >
                        </div>
                        <div class="form-text text-secondary">
                            Hinweis: Bei mehr als 6 Stunden Arbeitszeit sind mindestens 30 Minuten Pause erforderlich.
                        </div>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane me-2" aria-hidden="true"></i>
                            Anfrage senden
                        </button>
                        <a href="{{ route('users.dashboard', ['year' => \Carbon\Carbon::parse($date)->year, 'month' => \Carbon\Carbon::parse($date)->month]) }}" class="btn btn-outline-secondary">
                            Abbrechen
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
