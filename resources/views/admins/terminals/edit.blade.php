@extends('master.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                <div>
                    <h3 class="mb-1 fw-semibold text-secondary">
                        <i class="fa fa-desktop me-2" aria-hidden="true"></i>
                        Terminal bearbeiten
                    </h3>
                    <div class="text-secondary opacity-75">Passe Namen und Einstellungen des Terminals an.</div>
                </div>

                <a href="{{ route('terminals.index') }}" class="btn btn-outline-primary">
                    <i class="fa fa-arrow-left me-2" aria-hidden="true"></i>
                    Zur Übersicht
                </a>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-secondary text-white py-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa fa-pencil" aria-hidden="true"></i>
                        <span class="fw-semibold">Details</span>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5 bg-light">
                    <form method="POST" action="{{ route('terminals.update', $terminal->id) }}" class="mb-0">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="form-label fw-semibold text-secondary">Name</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                    <i class="fa fa-tag"></i>
                                </span>
                                <input
                                    id="name"
                                    type="text"
                                    class="form-control"
                                    name="name"
                                    value="{{ $terminal->name }}"
                                    required
                                >
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary" for="api_token">API-Token</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input id="api_token" type="text" class="form-control font-monospace" value="{{ $terminal->api_token }}" readonly>
                            </div>
                            <div class="form-text">Wird vom Terminal als <span class="font-monospace">device_token</span> gesendet.</div>
                        </div>

                        <div class="mb-4">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <div class="fw-semibold text-secondary">
                                                <i class="fa fa-toggle-on me-2" aria-hidden="true"></i>
                                                Status
                                            </div>
                                            <div class="text-secondary opacity-75">
                                                Deaktivierte Terminals dürfen keine RFID-Scans senden.
                                            </div>
                                        </div>

                                        <div class="form-check form-switch m-0">
                                            @php
                                                $enabledValue = old('enabled', ($terminal->enabled ?? true) ? '1' : '0');
                                            @endphp
                                            <input type="hidden" name="enabled" value="0">
                                            <input class="form-check-input" type="checkbox" name="enabled" id="enabled" value="1" {{ $enabledValue === '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enabled">Aktiv</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <a href="{{ route('terminals.index') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-times me-2" aria-hidden="true"></i>
                                Abbrechen
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-2" aria-hidden="true"></i>
                                Speichern
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
