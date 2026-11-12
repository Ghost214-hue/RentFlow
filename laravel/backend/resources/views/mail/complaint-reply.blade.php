@extends('mail.layout')

@section('title', 'Complaint update')

@section('body')
    <p style="margin:0 0 16px;">Dear {{ $tenant }},</p>

    <p style="margin:0 0 16px;">There is an update on your complaint.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
        <tr><td style="padding:14px 16px; color:#64748b; font-size:13px;">Reference</td>
            <td style="padding:14px 0; text-align:right; color:#1d4ed8; font-weight:700;">#{{ $id }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#64748b; font-size:13px;">Category</td>
            <td style="padding:0 16px 14px; text-align:right; color:#0f172a; font-weight:600;">{{ $category }}</td></tr>
    </table>

    <p style="margin:0 0 20px;">
        <a href="{{ $link }}" style="color:#1d4ed8; font-weight:600;">View the update in your portal &rarr;</a>
    </p>

    <p style="margin:0; color:#475569;">If you need to add anything, reply through the portal or contact management directly.</p>
@endsection