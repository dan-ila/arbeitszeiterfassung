@extends('layouts.email')

@section('content')
    <p style="margin-top:0;">Hallo <strong>{{ $user->first_name }}</strong>,</p>

    <p>
        👋 für dich wurde ein Konto bei <strong>{{ config('app.name') }}</strong> erstellt.
        Bitte setze jetzt dein Passwort, um loszulegen.
    </p>

    <div style="margin:18px 0 22px; padding:14px 16px; background:#F4F4F4; border:1px solid #e6eaee; border-radius:10px;">
        <div style="font-weight:bold; color:#005461; margin-bottom:6px;">🔐 Passwort setzen</div>
        <div style="color:#3B4953;">Klicke auf den Button und wähle ein neues Passwort.</div>
    </div>

    <p style="text-align:center; margin:26px 0;">
        <a href="{{ $resetUrl }}"
           style="
                display:inline-block;
                padding:12px 22px;
                background:#018790;
                color:#ffffff;
                text-decoration:none;
                border-radius:10px;
                font-weight:bold;
                letter-spacing:0.2px;
           ">
            Passwort setzen
        </a>
    </p>

    <p style="margin-bottom:0;">
        ⏱️ Dieser Link ist nur für eine begrenzte Zeit gültig.
        Wenn du diese E-Mail nicht erwartet hast, kannst du sie ignorieren.
    </p>

    <p style="margin-top:22px;">
        Viele Grüße<br>
        <strong>{{ config('app.name') }} Team</strong>
    </p>
@endsection
