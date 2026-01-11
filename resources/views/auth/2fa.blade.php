@extends('master.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-secondary text-white text-center py-4">
                    <h5 class="mb-1 fw-semibold">
                        <i class="fa fa-shield me-2" aria-hidden="true"></i>
                        Zwei-Faktor-Authentifizierung
                    </h5>
                    <small class="opacity-75">Bitte bestätige den Code aus deiner Authenticator-App.</small>
                </div>

                <div class="card-body p-4 p-md-5 bg-light">
                    @isset($qrCodeSvg)
                        <div class="text-center mb-4">
                            <p class="mb-2 text-secondary">
                                Scanne diesen QR-Code in deiner Authenticator-App:
                            </p>
                            <div class="d-inline-block p-3 bg-white border rounded-4 shadow-sm">{!! $qrCodeSvg !!}</div>
                        </div>
                    @endisset

                    <form method="POST" action="{{ route('2fa.verify') }}" class="mb-0">
                        @csrf
                        <label for="otp" class="form-label fw-semibold text-secondary">6-stelliger Code</label>
                        <div class="input-group input-group-lg mb-4">
                            <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                <i class="fa fa-key"></i>
                            </span>
                            <input
                                type="text"
                                name="one_time_password"
                                id="otp"
                                class="form-control text-center"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                placeholder="123456"
                                required
                            >
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-check me-2" aria-hidden="true"></i>
                                Bestätigen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
