@extends('mail.layout')

@section('title', 'Tenancy closed')

@section('body')
    <p style="margin:0 0 16px;">Dear {{ $tenant }},</p>

    <p style="margin:0 0 16px;">Your tenancy has been closed and your occupancy record updated. Thank you for your tenancy with us.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px;">
        <tr><td style="padding:14px 16px; color:#166534; font-size:13px;">Property</td>
            <td style="padding:14px 0; color:#14532d; font-weight:600;">{{ $property }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#166534; font-size:13px;">Unit</td>
            <td style="padding:0 16px 14px; color:#14532d; font-weight:600;">{{ $house }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#166534; font-size:13px;">Closed on</td>
            <td style="padding:0 16px 14px; color:#14532d; font-weight:600;">{{ $date }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#166534; font-size:13px;">National ID</td>
            <td style="padding:0 16px 14px; color:#14532d;">{{ $national_id }}</td></tr>
    </table>

    <p style="margin:0 0 8px; font-weight:600;">Please confirm before you close out</p>
    <ul style="margin:0 0 20px; padding-left:20px; color:#334155;">
        <li style="margin-bottom:6px;">All balances settled</li>
        <li style="margin-bottom:6px;">Keys and access devices returned</li>
        <li>Personal belongings removed</li>
    </ul>

    <p style="margin:0; color:#475569;">If you need tenancy records, a payment statement or a reference letter, just ask &mdash; we will provide them.</p>
@endsection