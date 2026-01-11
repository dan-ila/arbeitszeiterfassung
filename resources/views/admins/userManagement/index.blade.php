@extends('master.app')
@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h3 class="mb-1 fw-semibold text-secondary">
                <i class="fa fa-users me-2" aria-hidden="true"></i>
                Benutzerverwaltung
            </h3>
            <div class="text-secondary opacity-75">Benutzer anlegen, bearbeiten und exportieren.</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.user.management.create') }}" class="btn btn-primary">
                <i class="fa fa-user-plus me-2" aria-hidden="true"></i>
                Benutzer hinzufügen
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-secondary text-white">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div class="fw-semibold">
                    <i class="fa fa-list-alt me-2" aria-hidden="true"></i>
                    Übersicht
                </div>

                <form class="d-flex" role="search" method="GET" action="{{ url()->current() }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-secondary" aria-hidden="true">
                            <i class="fa fa-search"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Suche..."
                            aria-label="Suche"
                        >
                        <select class="form-select" name="role" aria-label="Rolle">
                            <option value="">Alle Rollen</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                        </select>
                        <button class="btn btn-primary" type="submit" aria-label="Suchen">
                            Suchen
                        </button>
                        <a class="btn btn-outline-secondary" href="{{ url()->current() }}" aria-label="Zurücksetzen" title="Zurücksetzen">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body p-0 bg-light">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-secondary">ID</th>
                            <th class="text-secondary">Vorname</th>
                            <th class="text-secondary">Nachname</th>
                            <th class="text-secondary">E-Mail</th>
                            <th class="text-secondary">Rolle</th>
                            <th class="text-secondary">Erstellt</th>
                            <th class="text-secondary text-end">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        @forelse($users as $user)
                            @php
                                $roleValue = strtolower((string) ($user->role ?? 'user'));
                                $roleLabel = match ($roleValue) {
                                    'admin' => 'Admin',
                                    'manager' => 'Manager',
                                    default => 'User',
                                };
                                $roleBadge = match ($roleValue) {
                                    'admin' => 'text-bg-danger',
                                    'manager' => 'text-bg-primary',
                                    default => 'text-bg-secondary',
                                };
                            @endphp
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $user->id }}</td>
                                <td class="text-secondary">{{ $user->first_name }}</td>
                                <td class="text-secondary">{{ $user->last_name }}</td>
                                <td class="text-secondary">{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $roleBadge }}">{{ $roleLabel }}</span>
                                </td>
                                <td class="text-secondary">{{ $user->created_at }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a
                                            href="{{ route('admin.user.management.edit', $user->id) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Bearbeiten"
                                            aria-label="Bearbeiten"
                                        >
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </a>

                                        <a
                                            href="{{ route('users.dashboard.export', [
                                                'user' => $user->id,
                                                'month' => request('month', now()->month),
                                                'year'  => request('year', now()->year),
                                            ]) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Arbeitszeiten exportieren (aktueller Monat)"
                                            aria-label="Export"
                                        >
                                            <i class="fa fa-file-excel-o" aria-hidden="true"></i>
                                        </a>

                                            <form action="{{ route('admin.user.management.destroy', $user->id) }}" method="POST" data-confirm="Möchtest du diesen Benutzer wirklich löschen?" data-confirm-title="Benutzer löschen" data-confirm-button="Löschen" data-confirm-variant="danger" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Löschen"
                                                aria-label="Löschen"
                                            >
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-5">
                                    <i class="fa fa-inbox me-2" aria-hidden="true"></i>
                                    Keine Benutzer gefunden.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($users, 'links'))
                <div class="p-3">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
