@extends('mail.layout')

@section('title', 'Payment received')

@section('body')
    <p style="margin:0 0 16px;">Dear {{ $tenant }},</p>

    <p style="margin:0 0 16px;">Thank you. Your payment has been received and recorded.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px;">
        <tr><td style="padding:14px 16px; color:#166534; font-size:13px;">Amount paid</td>
            <td style="padding:14px 16px; text-align:right; color:#14532d; font-size:18px; font-weight:700;">KES {{ $amount }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#166534; font-size:13px;">Category</td>
            <td style="padding:0 16px 14px; text-align:right; color:#14532d; font-weight:600;">{{ $category }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#166534; font-size:13px;">Date</td>
            <td style="padding:0 16px 14px; text-align:right; color:#14532d; font-weight:600;">{{ $date }}</td></tr>
        <tr><td style="padding:0 16px 14px; color:#166534; font-size:13px;">Account balance</td>
            <td style="padding:0 16px 14px; text-align:right; color:#14532d; font-weight:700;">KES {{ $balance }}</td></tr>
    </table>

    {{-- Only rendered when the payment actually settled a bill. An empty
         invoice block reads like a broken email, so it is omitted instead. --}}
    @isset($invoice_section)
        @if ($invoice_section)
            {{-- Escaped, not raw: invoice_section is assembled from bill data, but
                 an owner controls the underlying descriptions. `{!! !!}` here would
                 let them inject markup into tenants' mailboxes -- exactly what the
                 template-override path refuses to allow. pre-line keeps line breaks. --}}
            <div style="margin:0 0 20px; padding:16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; white-space:pre-line;">
                <p style="margin:0 0 10px; font-weight:600; color:#0f172a;">What this payment settled</p>
                {{ $invoice_section }}
            </div>
        @endif
    @endisset

    @isset($invoice_url)
        @if ($invoice_url)
            <p style="margin:0 0 20px;">
                <a href="{{ $invoice_url }}" style="color:#1d4ed8; font-weight:600;">View or download your invoice &rarr;</a>
            </p>
        @endif
    @endisset

    <p style="margin:0; color:#475569;">If the amount or balance looks wrong, contact your property manager with this email so we can check it quickly.</p>
@endsection