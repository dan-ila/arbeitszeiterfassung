<nav class="navbar navbar-expand-lg bg-light border-bottom shadow-sm">
  <div class="container-fluid py-1">
    <a class="navbar-brand" href="{{ route('users.dashboard') }}">
      <img src="{{ asset('logo.png') }}" width="60" height="45" class="d-inline-block align-text-top" alt="MSC">
    </a>

    @if (Auth::check())
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
              aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-lg-1">
          <li class="nav-item">
            <a class="nav-link text-secondary d-flex align-items-center gap-2 {{ request()->routeIs('users.dashboard') ? 'active' : '' }}" aria-current="page" href="{{ route('users.dashboard') }}">
              <i class="fa fa-dashboard" aria-hidden="true"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-secondary d-flex align-items-center gap-2 {{ request()->routeIs('users.settings') ? 'active' : '' }}" href="{{ route('users.settings') }}">
              <i class="fa fa-cog" aria-hidden="true"></i>
              <span>Einstellungen</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-secondary d-flex align-items-center gap-2 {{ request()->routeIs('users.vacation') ? 'active' : '' }}" href="{{ route('users.vacation') }}">
              <i class="fa fa-plane" aria-hidden="true"></i>
              <span>Urlaub</span>
            </a>
          </li>
          @if (Auth::user()->isAdmin())
          <li class="nav-item d-none d-lg-flex align-items-center">
            <div class="vr mx-2 text-primary opacity-100"></div>
          </li>
            <li class="nav-item">
            <a class="nav-link text-secondary d-flex align-items-center gap-2 {{ request()->routeIs('terminals.index') ? 'active' : '' }}" href="{{ route('terminals.index') }}">
              <i class="fa fa-desktop" aria-hidden="true"></i>
              <span>Terminals</span>
            </a>
            </li>
            <li class="nav-item">
            <a class="nav-link text-secondary d-flex align-items-center gap-2 {{ request()->routeIs('admin.user.management') ? 'active' : '' }}" href="{{ route('admin.user.management') }}">
              <i class="fa fa-users" aria-hidden="true"></i>
              <span>Benutzer</span>
            </a>
            </li>
            <li class="nav-item">
            <a class="nav-link text-secondary d-flex align-items-center gap-2 {{ request()->routeIs('admin.logs.index') ? 'active' : '' }}" href="{{ route('admin.logs.index') }}">
              <i class="fa fa-file-text-o" aria-hidden="true"></i>
              <span>Logs</span>
            </a>
            </li>
            <li class="nav-item">
            <a class="nav-link text-secondary d-flex align-items-center gap-2 {{ request()->routeIs('admin.worktime.requests.*') ? 'active' : '' }}" href="{{ route('admin.worktime.requests.index') }}">
              <i class="fa fa-check-square-o" aria-hidden="true"></i>
              <span>Anfragen</span>
            </a>
            </li>
          @elseif (Auth::user()->isManager())
          <li class="nav-item d-none d-lg-flex align-items-center">
            <div class="vr mx-2 text-primary opacity-100"></div>
          </li>
          <li class="nav-item">
            <a class="nav-link text-secondary d-flex align-items-center gap-2 {{ request()->routeIs('admin.worktime.requests.*') ? 'active' : '' }}" href="{{ route('admin.worktime.requests.index') }}">
              <i class="fa fa-check-square-o" aria-hidden="true"></i>
              <span>Anfragen</span>
            </a>
          </li>
          @endif
        </ul>

        <!-- User dropdown -->
        <div class="dropdown">
          <button class="btn btn-primary d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-user-circle" aria-hidden="true"></i>
            <span class="d-none d-sm-inline">Konto</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit" class="dropdown-item text-secondary d-flex align-items-center gap-2">
                      <i class="fa fa-sign-out" aria-hidden="true"></i>
                      <span>Abmelden</span>
                  </button>
              </form>
            </li>
          </ul>
        </div>
      </div>
    @endif
  </div>
</nav>
