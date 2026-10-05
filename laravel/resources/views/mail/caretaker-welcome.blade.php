@extends('mail.layout')

@section('title', 'Caretaker account activation')

@section('body')
    <p style="margin:0 0 16px;">Dear {{ $name }},</p>

    <p style="margin:0 0 16px;">
        You have been registered as a caretaker. Thank you for helping keep these properties safe, organised and well managed.
    </p>

    <p style="margin:0 0 8px; color:#475569;">Registered email:</p>
    <p style="margin:0 0 20px; font-weight:600;">{{ $email }}</p>

    <p style="margin:0 0 16px;">Your account lets you:</p>
    <ul style="margin:0 0 20px; padding-left:20px; color:#334155;">
        <li style="margin-bottom:6px;">Monitor occupancy and tenant activity</li>
        <li style="margin-bottom:6px;">Report maintenance and repairs</li>
        <li style="margin-bottom:6px;">Receive management notices</li>
        <li>Assist with inspections and property administration</li>
    </ul>

    <p style="margin:0 0 20px;">
        <a href="{{ $setup_link }}"
           style="display:inline-block; padding:12px 24px; background:#1d4ed8; color:#ffffff; font-weight:600; font-size:15px; text-decoration:none; border-radius:8px;">Activate my account</a>
    </p>

    <p style="margin:0; color:#64748b; font-size:13px;">This link expires in 48 hours. Contact your administrator if you need a replacement.</p>
@endsection