@extends('mail.layout')

@section('title', 'Rent reminder')

@section('body')
    <p style="margin:0 0 16px;">Dear {{ $tenant }},</p>

    <p style="margin:0 0 16px;">A friendly reminder that rent for <strong>{{ $month }}</strong> is due soon.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px;">
        <tr><td style="padding:14px 16px; color:#1e40af; font-size:13px;">Amount due</td>
            <td style="padding:14px 16px; text-align:right; color:#1e3a8a; font-size:18px; font-weight:700;">KES {{ $amount }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#1e40af; font-size:13px;">Account balance</td>
            <td style="padding:0 16px 14px; text-align:right; color:#1e3a8a; font-weight:700;">KES {{ $balance }}</td></tr>
    </table>

    <p style="margin:0 0 8px; font-weight:600;">How to pay</p>
    {{-- Escaped: payment_instructions is built from the property's payment
         details, which the owner controls. white-space:pre-line keeps the breaks. --}}
    <div style="margin:0 0 20px; padding:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; color:#334155; white-space:pre-line;">
        {{ $payment_instructions }}
    </div>

    <p style="margin:0 0 16px;">Paying on or before the due date avoids late fees and follow-up notices.</p>
    <p style="margin:0; color:#64748b; font-size:13px;">Already paid? Ignore this. If a payment has not shown on your account, send your confirmation to management.</p>
@endsection