@php
    $flashMap = [
        'success' => 'success',
        'error' => 'danger',
        'warning' => 'warning',
        'info' => 'info',
        'status' => 'info',
    ];

    $iconMap = [
        'success' => 'check-circle',
        'error' => 'exclamation-triangle',
        'warning' => 'exclamation-circle',
        'info' => 'info-circle',
        'status' => 'info-circle',
    ];
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle me-2" aria-hidden="true"></i>
        <strong>Bitte prüfen:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@foreach ($flashMap as $key => $variant)
    @if (session($key))
        <div class="alert alert-{{ $variant }}">
            <i class="fa fa-{{ $iconMap[$key] ?? 'info-circle' }} me-2" aria-hidden="true"></i>
            {{ session($key) }}
        </div>
    @endif
@endforeach
