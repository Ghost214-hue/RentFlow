@extends('mail.layout')

@section('title', 'Tenancy termination notice')

@section('body')
    <p style="margin:0 0 16px;">Dear {{ $tenant_name }},</p>

    <p style="margin:0 0 16px;">This is formal notice that your tenancy has been ended.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px; background:#fef2f2; border:1px solid #fecaca; border-radius:10px;">
        <tr><td style="padding:14px 16px; color:#991b1b; font-size:13px;">Property</td>
            <td style="padding:14px 0; color:#7f1d1d; font-weight:600;">{{ $property }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#991b1b; font-size:13px;">Unit</td>
            <td style="padding:0 16px 14px; color:#7f1d1d; font-weight:600;">{{ $house }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#991b1b; font-size:13px;">Termination date</td>
            <td style="padding:0 16px 14px; color:#7f1d1d; font-weight:700;">{{ $date }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#991b1b; font-size:13px;">Reason</td>
            <td style="padding:0 16px 14px; color:#7f1d1d;">{{ $reason }}</td></tr>
    </table>

    <p style="margin:0 0 8px; font-weight:600;">Before you vacate</p>
    <ul style="margin:0 0 20px; padding-left:20px; color:#334155;">
        <li style="margin-bottom:6px;">Settle any outstanding balance</li>
        <li style="margin-bottom:6px;">Remove all personal belongings</li>
        <li style="margin-bottom:6px;">Return all keys and access devices</li>
        <li>Report any damage</li>
    </ul>

    <p style="margin:0; color:#475569;">Questions about the move-out process or your final account? Contact {{ $owner_name }}.</p>
@endsection

@section('footer', 'Sent by {{ $owner_name }} via RentFlow.')