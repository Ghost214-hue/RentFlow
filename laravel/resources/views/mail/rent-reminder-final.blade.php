@extends('mail.layout')

@section('title', 'Rent due tomorrow')

@section('body')
    <p style="margin:0 0 16px;">Dear {{ $tenant }},</p>

    {{-- Deliberately a firmer visual treatment than the ordinary reminder: this
         is the last one before the due date. --}}
    <div style="margin:0 0 20px; padding:14px 16px; background:#fef2f2; border-left:3px solid #dc2626; border-radius:6px; color:#991b1b;">
        <strong>Rent for {{ $month }} is due tomorrow.</strong>
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
        <tr><td style="padding:14px 16px; color:#64748b; font-size:13px;">Amount due</td>
            <td style="padding:14px 16px; text-align:right; color:#0f172a; font-size:18px; font-weight:700;">KES {{ $amount }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#64748b; font-size:13px;">Account balance</td>
            <td style="padding:0 16px 14px; text-align:right; color:#0f172a; font-weight:700;">KES {{ $balance }}</td></tr>
    </table>

    <p style="margin:0 0 8px; font-weight:600;">How to pay</p>
    {{-- Escaped: payment_instructions is built from the property's payment
         details, which the owner controls. white-space:pre-line keeps the breaks. --}}
    <div style="margin:0 0 20px; padding:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; color:#334155; white-space:pre-line;">
        {{ $payment_instructions }}
    </div>

    <p style="margin:0; color:#475569;">Settling before the due date avoids penalties and further reminders. If you have already paid, send your confirmation.</p>
@endsection