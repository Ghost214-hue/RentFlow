@extends('mail.layout')

@section('title', 'Welcome to RentFlow')

@section('body')
    <p style="margin:0 0 16px;">Dear {{ $tenant }},</p>

    <p style="margin:0 0 16px;">Welcome to <strong>{{ $property }}</strong>. We are glad to have you as a tenant.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
        <tr><td style="padding:14px 16px; color:#64748b; font-size:13px; width:120px;">Property</td>
            <td style="padding:14px 0; color:#0f172a; font-weight:600;">{{ $property }}</td></tr>
        <tr><td style="padding:14px 16px; color:#64748b; font-size:13px;">Unit</td>
            <td style="padding:14px 0; color:#0f172a; font-weight:600;">{{ $house }}</td></tr>
        <tr><td style="padding:14px 16px; color:#64748b; font-size:13px;">Email</td>
            <td style="padding:14px 0; color:#0f172a; font-weight:600;">{{ $email }}</td></tr>
    </table>

    <p style="margin:0 0 8px; font-weight:600;">Set up your account</p>
    <p style="margin:0 0 16px; color:#475569;">Use the button below to choose a password and activate your tenant portal.</p>

    <p style="margin:0 0 20px;">
        <a href="{{ $setup_link }}"
           style="display:inline-block; padding:12px 24px; background:#1d4ed8; color:#ffffff; font-weight:600; font-size:15px; text-decoration:none; border-radius:8px;">Activate my account</a>
    </p>

    <p style="margin:0 0 16px; color:#475569;">The link expires in 48 hours. If it expires, ask your property manager for a new one.</p>

    <p style="margin:0; color:#334155;">Once active you can track rent and balances, raise maintenance requests and complaints, download invoices, and receive property notices.</p>
@endsection