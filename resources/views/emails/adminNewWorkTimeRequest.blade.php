@extends('layouts.email')

@section('content')
    @php
        $user = $req->user;
        $clockIn = \Carbon\Carbon::parse($req->requested_clock_in);
        $clockOut = \Carbon\Carbon::parse($req->requested_clock_out);
        $typeText = $req->type === 'add' ? 'Hinzufügen' : 'Ändern';
    @endphp

    <p style="margin-top:0;">Hallo Admin-Team,</p>

    <p>
        es gibt eine neue <strong>Arbeitszeit-Anfrage</strong> von
        <strong>{{ $user?->first_name }} {{ $user?->last_name }}</strong>.
    </p>

    <div style="margin:18px 0 22px; padding:14px 16px; background:#F4F4F4; border:1px solid #e6eaee; border-radius:10px;">
        <div style="font-weight:bold; color:#005461; margin-bottom:6px;">🕒 Details</div>
        <div style="color:#3B4953;">
            <div><strong>Typ:</strong> {{ $typeText }}</div>
            <div><strong>Datum:</strong> {{ $clockIn->format('d.m.Y') }}</div>
            <div><strong>Zeit:</strong> {{ $clockIn->format('H:i') }} – {{ $clockOut->format('H:i') }}</div>
            <div><strong>Pause:</strong> {{ (int)($req->requested_break_minutes ?? 0) }} Min</div>
            <div><strong>Grund:</strong> {{ $req->reason ?? '—' }}</div>
        </div>
    </div>

    <p style="text-align:center; margin:26px 0;">
        <a href="{{ route('admin.worktime.requests.index') }}"
           style="display:inline-block; padding:12px 22px; background:#018790; color:#ffffff; text-decoration:none; border-radius:10px; font-weight:bold;">
            Zu den Anfragen
        </a>
    </p>

    <p style="margin-top:22px;">
        Viele Grüße<br>
        <strong>{{ config('app.name') }}</strong>
    </p>
@endsection
