@extends('master.app')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h3 class="mb-1 fw-semibold text-secondary">
                <i class="fa fa-desktop me-2" aria-hidden="true"></i>
                Terminals
            </h3>
            <div class="text-secondary opacity-75">Verwalte Terminals und API-Zugänge.</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('terminals.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-2" aria-hidden="true"></i>
                Terminal hinzufügen
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-secondary text-white">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <span class="fw-semibold">
                    <i class="fa fa-list-alt me-2" aria-hidden="true"></i>
                    Übersicht
                </span>

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
                            placeholder="Name oder Token..."
                            aria-label="Suche"
                        >
                        <button class="btn btn-primary" type="submit">Suchen</button>
                        <a class="btn btn-outline-secondary" href="{{ url()->current() }}" title="Zurücksetzen" aria-label="Zurücksetzen">
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
                            <th class="text-secondary">Name</th>
                            <th class="text-secondary">Status</th>
                            <th class="text-secondary">API-Token</th>
                            <th class="text-secondary">Erstellt</th>
                            <th class="text-secondary">Aktualisiert</th>
                            <th class="text-secondary text-end">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        @forelse($terminals as $terminal)
                            <tr>
                                <td class="text-secondary fw-semibold">{{ $terminal->id }}</td>
                                <td class="text-secondary">{{ $terminal->name }}</td>
                                <td>
                                    @if(($terminal->enabled ?? true))
                                        <span class="badge text-bg-success">Aktiv</span>
                                    @else
                                        <span class="badge text-bg-secondary">Deaktiviert</span>
                                    @endif
                                </td>
                                <td class="text-secondary"><span class="font-monospace">{{ $terminal->api_token }}</span></td>
                                <td class="text-secondary">{{ $terminal->created_at }}</td>
                                <td class="text-secondary">{{ $terminal->updated_at }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        @if(($terminal->enabled ?? true))
                                            <form action="{{ route('terminals.disable', $terminal->id) }}" method="POST" data-confirm="Terminal wirklich deaktivieren?" data-confirm-title="Terminal deaktivieren" data-confirm-button="Deaktivieren" data-confirm-variant="warning" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Deaktivieren" aria-label="Deaktivieren">
                                                    <i class="fa fa-ban" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('terminals.enable', $terminal->id) }}" method="POST" data-confirm="Terminal wirklich aktivieren?" data-confirm-title="Terminal aktivieren" data-confirm-button="Aktivieren" data-confirm-variant="success" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Aktivieren" aria-label="Aktivieren">
                                                    <i class="fa fa-check" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('terminals.edit', $terminal->id) }}" class="btn btn-sm btn-outline-primary" title="Bearbeiten" aria-label="Bearbeiten">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </a>
                                        <form action="{{ route('terminals.destroy', $terminal->id) }}" method="POST" data-confirm="Möchtest du dieses Terminal wirklich löschen?" data-confirm-title="Terminal löschen" data-confirm-button="Löschen" data-confirm-variant="danger" class="d-inline">
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
                                    Keine Terminals gefunden.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($terminals, 'links'))
                <div class="p-3">
                    {{ $terminals->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
