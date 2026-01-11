@extends('master.app')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                <div>
                    <h3 class="mb-1 fw-semibold text-secondary">
                        <i class="fa fa-user-plus me-2" aria-hidden="true"></i>
                        Benutzer anlegen
                    </h3>
                    <div class="text-secondary opacity-75">Erstelle einen neuen Benutzer und sende die Einladungs-E-Mail.</div>
                </div>

                <a href="{{ route('admin.user.management') }}" class="btn btn-outline-primary">
                    <i class="fa fa-arrow-left me-2" aria-hidden="true"></i>
                    Zur Übersicht
                </a>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-secondary text-white py-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa fa-id-card" aria-hidden="true"></i>
                        <span class="fw-semibold">Benutzerdaten</span>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5 bg-light">
                    <form method="POST" action="{{ route('admin.user.management.store') }}" class="mb-0">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold text-secondary" for="first_name">Vorname</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-user"></i>
                                    </span>
                                    <input
                                        id="first_name"
                                        type="text"
                                        class="form-control"
                                        name="first_name"
                                        value="{{ old('first_name') }}"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold text-secondary" for="last_name">Nachname</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-user-o"></i>
                                    </span>
                                    <input
                                        id="last_name"
                                        type="text"
                                        class="form-control"
                                        name="last_name"
                                        value="{{ old('last_name') }}"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary" for="email">E-Mail</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-envelope"></i>
                                    </span>
                                    <input
                                        id="email"
                                        type="email"
                                        class="form-control"
                                        name="email"
                                        value="{{ old('email') }}"
                                        autocomplete="email"
                                        placeholder="name@firma.de"
                                        required
                                    >
                                </div>
                                <div class="form-text">An diese Adresse wird der Link zum Passwort-Setup gesendet.</div>
                            </div>

                            <div class="col-12">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body">
                                        <div class="fw-semibold text-secondary mb-2">
                                            <i class="fa fa-shield me-2" aria-hidden="true"></i>
                                            Rolle
                                        </div>
                                        <select class="form-select" name="role" required>
                                            <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>User</option>
                                            <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                        <div class="form-text">Manager kann Anfragen bearbeiten, Admin kann alles.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-paper-plane me-2" aria-hidden="true"></i>
                                Benutzer erstellen &amp; E-Mail senden
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
