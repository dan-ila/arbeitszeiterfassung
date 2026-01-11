@extends('master.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-secondary text-white text-center py-4">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fa fa-cog me-2" aria-hidden="true"></i>
                        Meine Einstellungen
                    </h5>
                </div>

                <div class="card-body p-4 p-md-5 bg-light">
                    <form method="POST" action="{{ route('users.settings.update') }}" class="mt-2">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold text-secondary" for="first_name">Vorname</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-id-card"></i>
                                    </span>
                                    <input
                                        id="first_name"
                                        type="text"
                                        class="form-control"
                                        name="first_name"
                                        value="{{ $user->first_name }}"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold text-secondary" for="last_name">Nachname</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-id-card-o"></i>
                                    </span>
                                    <input
                                        id="last_name"
                                        type="text"
                                        class="form-control"
                                        name="last_name"
                                        value="{{ $user->last_name }}"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary" for="email">E-Mail</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-envelope"></i>
                                    </span>
                                    <input
                                        id="email"
                                        type="email"
                                        class="form-control"
                                        name="email"
                                        value="{{ $user->email }}"
                                        autocomplete="email"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary" for="rfid_uid">RFID UID</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-tag"></i>
                                    </span>
                                    <input
                                        id="rfid_uid"
                                        type="text"
                                        class="form-control"
                                        name="rfid_uid"
                                        value="{{ $user->rfid_uid }}"
                                    >
                                </div>
                                <div class="form-text">Optional – nur wenn ein RFID-Tag verwendet wird.</div>
                            </div>

                            <div class="col-12">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                            <div>
                                                <div class="fw-semibold text-secondary">
                                                    <i class="fa fa-shield me-2" aria-hidden="true"></i>
                                                    Zwei-Faktor-Authentifizierung
                                                </div>
                                                <div class="text-secondary opacity-75">
                                                    Erhöhe die Sicherheit deines Kontos.
                                                </div>
                                            </div>

                                            <div class="form-check form-switch m-0">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="enable_2fa"
                                                    id="enable2fa"
                                                    {{ $user->two_factor_enabled ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label" for="enable2fa">Aktivieren</label>
                                            </div>
                                        </div>

                                        @if($user->two_factor_enabled && $qrCodeSvg)
                                            <hr>
                                            <div class="d-flex flex-column flex-md-row align-items-start gap-4">
                                                <div class="flex-grow-1">
                                                    <p class="mb-2 text-secondary">
                                                        Scanne diesen QR-Code in deiner Authenticator-App:
                                                    </p>
                                                    <div class="text-secondary opacity-75">
                                                        Danach kannst du beim Login den Code aus der App verwenden.
                                                    </div>
                                                </div>
                                                <div class="p-3 bg-white border rounded-3">
                                                    {!! $qrCodeSvg !!}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
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
