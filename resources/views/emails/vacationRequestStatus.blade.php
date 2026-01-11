@extends('layouts.email')

@section('content')
    @php
        $user = $vacation->user;
        $start = \Carbon\Carbon::parse($vacation->start_date);
        $end = \Carbon\Carbon::parse($vacation->end_date);
        $days = (int)($vacation->days ?? 0);
        $status = strtolower((string) $vacation->status);
        $statusText = match ($status) {
            'approved' => 'genehmigt',
            'rejected' => 'abgelehnt',
            default => $vacation->status,
        };
    @endphp

    <p style="margin-top:0;">Hallo <strong>{{ $user?->first_name ?? '...' }}</strong>,</p>

    <p>
        dein Urlaubsantrag wurde <strong>{{ $statusText }}</strong>.
    </p>

    <div style="margin:18px 0 22px; padding:14px 16px; background:#F4F4F4; border:1px solid #e6eaee; border-radius:10px;">
        <div style="font-weight:bold; color:#005461; margin-bottom:6px;">🏖️ Zeitraum</div>
        <div style="color:#3B4953;">
            <div><strong>Von:</strong> {{ $start->format('d.m.Y') }}</div>
            <div><strong>Bis:</strong> {{ $end->format('d.m.Y') }}</div>
            <div><strong>Tage:</strong> {{ $days }}</div>
        </div>
    </div>

    <p style="margin-top:22px;">
        Viele Grüße<br>
        <strong>{{ config('app.name') }} Team</strong>
    </p>
@endsection
