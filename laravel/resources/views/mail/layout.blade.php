{{--
    Base layout for every outbound email.

    Email clients are not browsers: no flexbox without hacks, no web fonts, and
    a hostile default of ~600px width. So this is deliberately boring -- a
    single centred table with inline styles, which is what Outlook and Gmail
    actually render correctly.

    Blade's {{ }} escapes every interpolated value. That is the whole reason the
    shipped templates are Blade rather than the plain-text strings the legacy
    install stored: a tenant name containing < would otherwise become markup.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'RentFlow')</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                   style="width:100%; max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.1);">
                <tr>
                    <td style="background:#1d4ed8; padding:20px 28px;">
                        <span style="color:#ffffff; font-size:18px; font-weight:700; letter-spacing:-.01em;">RentalFlow</span>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px; color:#0f172a; font-size:15px; line-height:1.65;">
                        @yield('body')
                    </td>
                </tr>

                <tr>
                    <td style="padding:18px 28px; background:#f8fafc; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; line-height:1.6;">
                        @yield('footer', 'This message was sent by your property manager through RentFlow.')
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>