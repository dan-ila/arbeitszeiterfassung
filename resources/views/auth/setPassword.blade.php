@extends('master.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-secondary text-white text-center py-4">
                    <h4 class="mb-1 fw-semibold">
                        <i class="fa fa-lock me-2" aria-hidden="true"></i>
                        Passwort festlegen
                    </h4>
                    <small class="opacity-75">Bitte wähle ein sicheres Passwort.</small>
                </div>

                <div class="card-body p-4 p-md-5 bg-light">
                    <form method="POST" action="{{ route('password.store') }}" class="mt-2">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold text-secondary">Passwort</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                    <i class="fa fa-key"></i>
                                </span>
                                <input
                                    id="password"
                                    type="password"
                                    class="form-control"
                                    name="password"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold text-secondary">Passwort bestätigen</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                    <i class="fa fa-check"></i>
                                </span>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    class="form-control"
                                    name="password_confirmation"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fa fa-save me-2" aria-hidden="true"></i>
                            Passwort speichern
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
