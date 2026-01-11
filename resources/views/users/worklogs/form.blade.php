@extends('master.app')
@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h3 class="mb-1 fw-semibold text-secondary">
                <i class="fa fa-plus-circle me-2" aria-hidden="true"></i>
                Schicht hinzufügen
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

    @if(($existingLogCount ?? 0) > 0)
        <div class="alert alert-info">
            <i class="fa fa-info-circle me-2" aria-hidden="true"></i>
            Für diesen Tag gibt es bereits {{ $existingLogCount }} Schicht(en). Du kannst eine weitere Schicht hinzufügen (ohne Überschneidung).
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-secondary text-white">
            <div class="d-flex align-items-center justify-content-between">
                <span class="fw-semibold">
                    <i class="fa fa-clock-o me-2" aria-hidden="true"></i>
                    Angaben
                </span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light" id="btnAddShift">
                        <i class="fa fa-plus me-1" aria-hidden="true"></i>
                        Schicht
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnAddBreak">
                        <i class="fa fa-coffee me-1" aria-hidden="true"></i>
                        Pause
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body bg-light">
            <form method="POST" action="{{ route('users.worklogs.store') }}">
                @csrf
                <input type="hidden" name="date" value="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}">

                @php
                    $oldBlocks = old('blocks');
                    if (!is_array($oldBlocks) || count($oldBlocks) === 0) {
                        $oldBlocks = [
                            ['type' => 'shift', 'start_time' => '', 'end_time' => ''],
                        ];
                    }
                @endphp

                <div class="form-text text-secondary mb-3">
                    Tipp: Du kannst Blöcke in Reihenfolge hinzufügen, z.B. <span class="fw-semibold">Schicht → Pause → Schicht</span>.
                    Hinweis: Bei mehr als 6 Stunden Gesamtarbeitszeit sind mindestens 30 Minuten Pause erforderlich.
                </div>

                <div id="blockRows" class="d-flex flex-column gap-3">
                    @foreach($oldBlocks as $i => $block)
                        @php
                            $type = $block['type'] ?? 'shift';
                        @endphp
                        <div class="card border-0 shadow-sm rounded-4" data-block-row data-block-type="{{ $type }}">
                            <div class="card-body">
                                <input type="hidden" name="blocks[{{ $i }}][type]" value="{{ $type }}">

                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-semibold text-secondary" data-block-title></div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-remove-block>
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                    </button>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label text-secondary">Beginn</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                @if($type === 'break')
                                                    <i class="fa fa-coffee" aria-hidden="true"></i>
                                                @else
                                                    <i class="fa fa-sign-in" aria-hidden="true"></i>
                                                @endif
                                            </span>
                                            <input type="time" name="blocks[{{ $i }}][start_time]" class="form-control" value="{{ $block['start_time'] ?? '' }}" required>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label text-secondary">Ende</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                @if($type === 'break')
                                                    <i class="fa fa-coffee" aria-hidden="true"></i>
                                                @else
                                                    <i class="fa fa-sign-out" aria-hidden="true"></i>
                                                @endif
                                            </span>
                                            <input type="time" name="blocks[{{ $i }}][end_time]" class="form-control" value="{{ $block['end_time'] ?? '' }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-2" aria-hidden="true"></i>
                        Speichern
                    </button>
                    <a href="{{ route('users.dashboard', ['year' => \Carbon\Carbon::parse($date)->year, 'month' => \Carbon\Carbon::parse($date)->month]) }}" class="btn btn-outline-secondary">
                        Abbrechen
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="blockShiftTemplate">
    <div class="card border-0 shadow-sm rounded-4" data-block-row data-block-type="shift">
        <div class="card-body">
            <input type="hidden" name="blocks[__INDEX__][type]" value="shift">

            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-semibold text-secondary" data-block-title></div>
                <button type="button" class="btn btn-sm btn-outline-danger" data-remove-block>
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </button>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label text-secondary">Beginn</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-sign-in" aria-hidden="true"></i></span>
                        <input type="time" name="blocks[__INDEX__][start_time]" class="form-control" required>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label text-secondary">Ende</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-sign-out" aria-hidden="true"></i></span>
                        <input type="time" name="blocks[__INDEX__][end_time]" class="form-control" required>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="blockBreakTemplate">
    <div class="card border-0 shadow-sm rounded-4" data-block-row data-block-type="break">
        <div class="card-body">
            <input type="hidden" name="blocks[__INDEX__][type]" value="break">

            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-semibold text-secondary" data-block-title></div>
                <button type="button" class="btn btn-sm btn-outline-danger" data-remove-block>
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </button>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label text-secondary">Beginn</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-coffee" aria-hidden="true"></i></span>
                        <input type="time" name="blocks[__INDEX__][start_time]" class="form-control" required>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label text-secondary">Ende</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-coffee" aria-hidden="true"></i></span>
                        <input type="time" name="blocks[__INDEX__][end_time]" class="form-control" required>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    (function () {
        const container = document.getElementById('blockRows');
        const addBtn = document.getElementById('btnAddShift');
        const addBreakBtn = document.getElementById('btnAddBreak');
        const shiftTpl = document.getElementById('blockShiftTemplate');
        const breakTpl = document.getElementById('blockBreakTemplate');

        function updateTitlesAndButtons() {
            const rows = Array.from(container.querySelectorAll('[data-block-row]'));
            let shiftNum = 0;
            let breakNum = 0;
            rows.forEach((row) => {
                const type = row.getAttribute('data-block-type');
                const titleEl = row.querySelector('[data-block-title]');
                if (!titleEl) return;

                if (type === 'break') {
                    breakNum += 1;
                    titleEl.innerHTML = '<i class="fa fa-coffee me-2" aria-hidden="true"></i>Pause ' + breakNum;
                } else {
                    shiftNum += 1;
                    titleEl.innerHTML = '<i class="fa fa-random me-2" aria-hidden="true"></i>Schicht ' + shiftNum;
                }
            });

            // At least 1 shift must remain
            const shiftRows = rows.filter((r) => r.getAttribute('data-block-type') !== 'break');
            const canRemoveShift = shiftRows.length > 1;
            rows.forEach((row) => {
                const btn = row.querySelector('[data-remove-block]');
                if (!btn) return;
                const type = row.getAttribute('data-block-type');
                btn.disabled = (type !== 'break') ? !canRemoveShift : false;
            });
        }

        function nextIndex() {
            const inputs = container.querySelectorAll('input[name^="blocks["]');
            let max = -1;
            inputs.forEach((input) => {
                const m = input.name.match(/^blocks\[(\d+)\]/);
                if (m) max = Math.max(max, parseInt(m[1], 10));
            });
            return max + 1;
        }

        function addShiftRow() {
            const idx = nextIndex();
            const html = shiftTpl.innerHTML
                .replaceAll('__INDEX__', String(idx));
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            container.appendChild(wrapper.firstElementChild);
            updateTitlesAndButtons();
        }

        function addBreakRow() {
            const idx = nextIndex();
            const html = breakTpl.innerHTML
                .replaceAll('__INDEX__', String(idx));
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            container.appendChild(wrapper.firstElementChild);
            updateTitlesAndButtons();
        }

        container.addEventListener('click', (e) => {
            const target = e.target;
            const btn = target && target.closest ? target.closest('[data-remove-block]') : null;
            if (!btn) return;
            const row = btn.closest('[data-block-row]');
            if (!row) return;
            row.remove();
            updateTitlesAndButtons();
        });

        addBtn.addEventListener('click', addShiftRow);
        addBreakBtn.addEventListener('click', addBreakRow);
        updateTitlesAndButtons();
    })();
</script>
@endsection
