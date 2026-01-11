@extends('master.app')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                <div>
                    <h3 class="mb-1 fw-semibold text-secondary">
                        <i class="fa fa-user me-2" aria-hidden="true"></i>
                        Benutzer bearbeiten
                    </h3>
                    <div class="text-secondary opacity-75">Profil- und Rolleninformationen aktualisieren.</div>
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
                    <form method="POST" action="{{ route('admin.user.management.update', $user->id) }}" class="mb-0">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold text-secondary" for="first_name">Vorname</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-user"></i>
                                    </span>
                                    <input id="first_name" type="text" class="form-control" name="first_name" value="{{ $user->first_name }}" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold text-secondary" for="last_name">Nachname</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-user-o"></i>
                                    </span>
                                    <input id="last_name" type="text" class="form-control" name="last_name" value="{{ $user->last_name }}" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary" for="email">E-Mail</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-envelope"></i>
                                    </span>
                                    <input id="email" type="email" class="form-control" name="email" value="{{ $user->email }}" autocomplete="email" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary" for="rfid_uid">RFID UID</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                                        <i class="fa fa-tag"></i>
                                    </span>
                                    <input id="rfid_uid" type="text" class="form-control" name="rfid_uid" value="{{ $user->rfid_uid ?? '' }}">
                                </div>
                                <div class="form-text">Optional – nur wenn ein RFID-Tag verwendet wird.</div>
                            </div>

                            <div class="col-12">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body">
                                        <div class="fw-semibold text-secondary mb-2">
                                            <i class="fa fa-shield me-2" aria-hidden="true"></i>
                                            Rolle
                                        </div>
                                        <select class="form-select" name="role" required>
                                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                            <option value="manager" {{ $user->role === 'manager' ? 'selected' : '' }}>Manager</option>
                                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                        <div class="form-text">Manager kann Anfragen bearbeiten, Admin kann alles.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.user.management') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-times me-2" aria-hidden="true"></i>
                                Abbrechen
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-2" aria-hidden="true"></i>
                                Speichern
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                                <div>
                                    <div class="fw-semibold text-secondary">
                                        <i class="fa fa-envelope me-2" aria-hidden="true"></i>
                                        Passwort-Link
                                    </div>
                                    <div class="text-secondary opacity-75">Sende einen Link zum Passwort-Setup an den Benutzer.</div>
                                </div>

                                <form action="{{ route('admin.user.management.sendPasswordLink', $user->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fa fa-paper-plane me-2" aria-hidden="true"></i>
                                        Passwort-Link senden
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
