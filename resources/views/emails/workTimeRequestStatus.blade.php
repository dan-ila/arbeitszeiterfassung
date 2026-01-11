@extends('layouts.email')

@section('content')
    @php
        $user = $req->user;
        $clockIn = \Carbon\Carbon::parse($req->requested_clock_in);
        $clockOut = \Carbon\Carbon::parse($req->requested_clock_out);
        $status = strtolower((string) $req->status);
        $statusText = match ($status) {
            'approved' => 'genehmigt',
            'rejected' => 'abgelehnt',
            default => $req->status,
        };
        $typeText = $req->type === 'add' ? 'Hinzufügen' : 'Ändern';
    @endphp

    <p style="margin-top:0;">Hallo <strong>{{ $user?->first_name ?? '...' }}</strong>,</p>

    <p>
        deine Arbeitszeit-Anfrage wurde <strong>{{ $statusText }}</strong>.
    </p>

    <div style="margin:18px 0 22px; padding:14px 16px; background:#F4F4F4; border:1px solid #e6eaee; border-radius:10px;">
        <div style="font-weight:bold; color:#005461; margin-bottom:6px;">🕒 Details</div>
        <div style="color:#3B4953;">
            <div><strong>Typ:</strong> {{ $typeText }}</div>
            <div><strong>Datum:</strong> {{ $clockIn->format('d.m.Y') }}</div>
            <div><strong>Zeit:</strong> {{ $clockIn->format('H:i') }} – {{ $clockOut->format('H:i') }}</div>
            <div><strong>Pause:</strong> {{ (int)($req->requested_break_minutes ?? 0) }} Min</div>
        </div>
    </div>

    @if($status === 'rejected' && !empty($req->admin_comment))
        <p><strong>Kommentar:</strong> {{ $req->admin_comment }}</p>
    @endif

    <p style="margin-top:22px;">
        Viele Grüße<br>
        <strong>{{ config('app.name') }} Team</strong>
    </p>
@endsection
