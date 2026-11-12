@extends('mail.layout')

@section('title', 'Password reset')

@section('body')
    <p style="margin:0 0 16px;">Dear {{ $name }},</p>

    <p style="margin:0 0 16px;">
        We received a request to reset the password on your RentFlow account.
    </p>

    <p style="margin:0 0 8px; color:#475569;">Your verification code is:</p>
    <div style="margin:0 0 20px; padding:18px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:10px; text-align:center;">
        <span style="font-size:30px; font-weight:700; letter-spacing:.28em; color:#0f172a; font-family:'SFMono-Regular',Consolas,monospace;">{{ $code }}</span>
    </div>

    <p style="margin:0 0 16px;">This code expires in <strong>{{ $expires }}</strong>.</p>

    <ol style="margin:0 0 16px; padding-left:20px; color:#334155;">
        <li style="margin-bottom:6px;">Enter the code above.</li>
        <li style="margin-bottom:6px;">Create a new password.</li>
        <li>Sign in with your new credentials.</li>
    </ol>

    {{-- The most important line in the email: it is what makes this not a phishing lure. --}}
    <div style="margin:0 0 16px; padding:14px 16px; background:#fffbeb; border-left:3px solid #f59e0b; border-radius:6px; color:#78350f; font-size:14px;">
        <strong>If you did not request this, ignore this email.</strong>
        No change will be made, and nobody can use this code without also knowing it &mdash; it is not sent to anyone else.
    </div>

    <p style="margin:0; color:#64748b; font-size:13px;">
        RentFlow will never ask you for this code by phone, SMS or in a reply to this email.
    </p>
@endsection