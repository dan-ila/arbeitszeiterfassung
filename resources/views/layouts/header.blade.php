<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ route('users.dashboard') }}">
      <img src="{{ asset('logo.png') }}" style="width: 60px; height: 45px;">
    </a>    
    @if (Auth::check())
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="">Dashboard</a>
          </li>
          <li>
              <a class="nav-link active" aria-current="page" href="">Arbeitszeit</a>
          </li>
          <li>
              <a class="nav-link active" aria-current="page" href="">Einstellungen</a>
          </li>
          @if (Auth::user()->isAdmin())
            <div class="verticalLine" style="border-left: 2px solid grey;"></div>
            <li>
                <a class="nav-link active" aria-current="page" href="">Adminbereich</a>
            </li>
            <li>
                <a class="nav-link active" aria-current="page" href="{{ route('admin.user.management') }}">Benutzer</a>
            </li>
            <li>
                <a class="nav-link active" aria-current="page" href="">Logs</a>
            </li>
          @endif
        </ul>
        <div class="dropdown">
          <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-user"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit" class="dropdown-item" style="ms-5">
                      Abmelden
                  </button>
              </form>
            </li>
          </ul>
        </div>
      </div>
    @endif
  </div>
</nav>
