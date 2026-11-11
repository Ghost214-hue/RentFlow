@extends('mail.layout')

@section('title', 'Lease renewal')

@section('body')
    <p style="margin:0 0 16px;">Dear {{ $tenant }},</p>

    <p style="margin:0 0 16px;">We hope you have enjoyed your stay at <strong>{{ $property }}</strong>.</p>

    <p style="margin:0 0 16px;">
        Our records show your tenancy for <strong>{{ $house }}</strong> is approaching its end date.
    </p>

    <p style="margin:0 0 8px; font-weight:600;">We would like to discuss renewal with you before it expires</p>
    <ul style="margin:0 0 20px; padding-left:20px; color:#334155;">
        <li style="margin-bottom:6px;">Renewal options and any updated terms</li>
        <li>Your plans beyond the current lease</li>
    </ul>

    <p style="margin:0 0 16px;">Getting in touch early makes the process smoother and gives everyone time to plan.</p>
    <p style="margin:0; color:#475569;">If you do not intend to renew, please let us know within the notice period in your tenancy agreement.</p>
@endsection