@extends('master.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-secondary text-white text-center py-4">
                    <h4 class="mb-0 fw-semibold">
                        <i class="fa fa-shield me-2" aria-hidden="true"></i>
                        Zwei-Faktor-Authentifizierung einrichten
                    </h4>
                </div>

                <div class="card-body p-4 p-md-5 bg-light">
                    <div class="text-center mb-4">
                        <p class="mb-2 text-secondary">
                            Scanne diesen QR-Code mit deiner Authenticator-App (z.B. Google Authenticator oder Authy).
                        </p>
                        <div class="text-secondary opacity-75">
                            Gib anschließend den 6-stelligen Code ein, um 2FA zu aktivieren.
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-lg-row align-items-center justify-content-center gap-4">
                        <div class="p-3 bg-white border rounded-4 shadow-sm">
                            {!! $qrCodeSvg !!}
                        </div>

                        <div class="w-100" style="max-width: 420px;">
                            <form method="POST" action="{{ route('2fa.enable') }}" class="mb-0">
                                @csrf

                                <label for="one_time_password" class="form-label fw-semibold text-secondary">
                                    Bestätigungscode
                                </label>
                                <div class="input-group input-group-lg mb-3">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-key"></i>
                                    </span>
                                    <input
                                        id="one_time_password"
                                        class="form-control text-center"
                                        name="one_time_password"
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        placeholder="123456"
                                        required
                                    >
                                </div>

                                <div class="d-grid">
                                    <button class="btn btn-primary btn-lg" type="submit">
                                        <i class="fa fa-check me-2" aria-hidden="true"></i>
                                        2FA aktivieren
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3 text-secondary">
                        <div class="col-12 col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa fa-mobile mt-1" aria-hidden="true"></i>
                                <div>
                                    <div class="fw-semibold">App öffnen</div>
                                    <div class="opacity-75">Authenticator-App auf dem Smartphone starten.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa fa-qrcode mt-1" aria-hidden="true"></i>
                                <div>
                                    <div class="fw-semibold">QR-Code scannen</div>
                                    <div class="opacity-75">Den QR-Code hinzufügen und speichern.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fa fa-unlock-alt mt-1" aria-hidden="true"></i>
                                <div>
                                    <div class="fw-semibold">Code bestätigen</div>
                                    <div class="opacity-75">Den 6-stelligen Code eingeben und aktivieren.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
