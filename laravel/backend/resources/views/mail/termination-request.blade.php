@extends('mail.layout')

@section('title', 'Termination request for review')

@section('body')
    <p style="margin:0 0 16px;">Dear {{ $owner_name }},</p>

    <p style="margin:0 0 16px;">A tenant has asked to end their tenancy. This needs your decision.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px; background:#fffbeb; border:1px solid #fde68a; border-radius:10px;">
        <tr><td style="padding:14px 16px; color:#92400e; font-size:13px;">Tenant</td>
            <td style="padding:14px 0; color:#78350f; font-weight:700;">{{ $tenant_name }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#92400e; font-size:13px;">Property</td>
            <td style="padding:0 16px 14px; color:#78350f; font-weight:600;">{{ $property }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#92400e; font-size:13px;">Unit</td>
            <td style="padding:0 16px 14px; color:#78350f; font-weight:600;">{{ $house }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#92400e; font-size:13px;">Requested date</td>
            <td style="padding:0 16px 14px; color:#78350f; font-weight:600;">{{ $date }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#92400e; font-size:13px;">Reason given</td>
            <td style="padding:0 16px 14px; color:#78350f;">{{ $reason }}</td></tr>
    </table>

    <p style="margin:0 0 16px;">Please review the request in your dashboard and approve or decline it. Nothing has changed yet &mdash; the tenancy is untouched until you act.</p>
    <p style="margin:0; color:#475569;">If you need more information, contact the tenant directly.</p>
@endsection