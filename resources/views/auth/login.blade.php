@extends('master.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 col-xxl-4">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-secondary text-white text-center py-4">
                    <h4 class="mb-1 fw-semibold">Anmeldung</h4>
                    <small class="opacity-75">Melde dich an, um fortzufahren</small>
                </div>

                <div class="card-body p-4 p-md-5 bg-light">
                    <form action="{{ route('login.post') }}" method="POST" class="mt-2">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Adresse</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="name@firma.de"
                                    autocomplete="email"
                                    required
                                >
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Passwort</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                    <i class="fa fa-lock"></i>
                                </span>
                                <input
                                    type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    id="password"
                                    name="password"
                                    placeholder="Dein Passwort"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fa fa-sign-in me-2" aria-hidden="true"></i>
                            Anmelden
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
