@extends('master.app')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                <div>
                    <h3 class="mb-1 fw-semibold text-secondary">
                        <i class="fa fa-plane me-2" aria-hidden="true"></i>
                        Urlaub beantragen
                    </h3>
                    <div class="text-secondary opacity-75">Wähle den Zeitraum für deinen Urlaubsantrag.</div>
                </div>

                <a href="{{ route('users.vacation') }}" class="btn btn-outline-primary">
                    <i class="fa fa-arrow-left me-2" aria-hidden="true"></i>
                    Zur Übersicht
                </a>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-secondary text-white py-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa fa-calendar" aria-hidden="true"></i>
                        <span class="fw-semibold">Zeitraum</span>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5 bg-light">
                    <form method="POST" action="{{ route('users.vacation.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="start_date" class="form-label fw-semibold text-secondary">Startdatum</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-play"></i>
                                    </span>
                                    <input
                                        type="date"
                                        id="start_date"
                                        name="start_date"
                                        class="form-control"
                                        value="{{ old('start_date') }}"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="end_date" class="form-label fw-semibold text-secondary">Enddatum</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-stop"></i>
                                    </span>
                                    <input
                                        type="date"
                                        id="end_date"
                                        name="end_date"
                                        class="form-control"
                                        value="{{ old('end_date') }}"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-4">
                            <div class="text-secondary opacity-75">
                                <i class="fa fa-info-circle me-1" aria-hidden="true"></i>
                                Der Antrag wird zur Prüfung eingereicht.
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-paper-plane me-2" aria-hidden="true"></i>
                                Beantragen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
