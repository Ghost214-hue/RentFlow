@extends('mail.layout')

@section('title', $title ?? 'Property notice')

@section('body')
    <p style="margin:0 0 16px;">Dear {{ $tenant_name }},</p>

    <p style="margin:0 0 16px;">You have received an important notice from {{ $sender_name }}.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
        <tr><td style="padding:14px 16px; color:#64748b; font-size:13px;">Property</td>
            <td style="padding:14px 0; color:#0f172a; font-weight:600;">{{ $property }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#64748b; font-size:13px;">Unit</td>
            <td style="padding:0 16px 14px; color:#0f172a; font-weight:600;">{{ $house }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#64748b; font-size:13px;">Date</td>
            <td style="padding:0 16px 14px; color:#0f172a; font-weight:600;">{{ $date }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#64748b; font-size:13px;">Category</td>
            <td style="padding:0 16px 14px; color:#0f172a; font-weight:600;">{{ $category }}</td></tr>
    </table>

    <p style="margin:0 0 8px; font-weight:600; font-size:16px; color:#0f172a;">{{ $title }}</p>

    {{-- Free text from an owner, so it is escaped rather than trusted. --}}
    <div style="margin:0 0 20px; padding:16px; background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; color:#334155;">
        {{ $description }}
    </div>

    <p style="margin:0; color:#475569;">Sign in to your RentFlow account for the full notice and any responses.</p>
@endsection