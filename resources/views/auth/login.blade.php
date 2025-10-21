@extends('master.app')

@section('content')
<div class="card" style="margin-top: 200px; margin-left: 300px; margin-right: 300px">
    <div class="card-header text-center">
        Anmeldung
    </div>
    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-3">
                <label for="email" class="form-label">Email Adresse</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                       id="email" name="email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Passwort</label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                       id="password" name="password" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="card-footer text-body-secondary d-grid gap-2">
            <button type="submit" class="btn btn-primary">Anmelden</button>
        </div>
    </form>
</div>
@endsection
